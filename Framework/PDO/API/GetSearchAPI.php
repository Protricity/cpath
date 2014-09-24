<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;


use CPath\Describable\IDescribable;
use CPath\Framework\API\Field\Field;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Field\Param;
use CPath\Framework\API\Field\RequiredField;
use CPath\Framework\API\Field\RequiredParam;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\API\Util\APIExecuteUtil;
use CPath\Framework\PDO\Misc\API_GetBrowseDescriptor;
use CPath\Framework\PDO\Interfaces\IAPIGetBrowseCallbacks;
use CPath\Framework\PDO\Interfaces\IPDOModelSearchRender;
use CPath\Framework\PDO\Interfaces\IReadAccess;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Query\PDOModelSelect;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

class GetSearchAPI implements IAPI {
    private $mSearchColumns;
    private $mTable;
    private $mPolicy;
    private $mLimitMax;
    private $mReturnColumns;

    /**
     * Construct an instance of the GET API
     * @param PDOPrimaryKeyTable $Table the table instance
     * @param string|array $searchColumns a column or array of columns that may be used to search for Models.
     * Note: PRIMARY key is already included
     * @param IReadAccess $SecurityPolicy
     * @param int $limitMax Maximum search limit
     */
    function __construct(PDOPrimaryKeyTable $Table, $searchColumns=NULL, IReadAccess $SecurityPolicy=null, $limitMax=100) {
        $this->mTable = $Table;
        $this->mSearchColumns = $searchColumns ?: $Table::COLUMN_ID ?: $Table::COLUMN_PRIMARY;
        $this->mPolicy = $SecurityPolicy;
        $this->mLimitMax = $limitMax;
    }

    /**
     * @return IReadAccess|PDOPrimaryKeyTable
     */
    function getTable() {
        return $this->mTable;
    }


    /**
     * Get all API Fields
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @throws \CPath\Framework\API\Exceptions\APIException
     * @return IField[]
     */
    function getFields(IRequest $Request) {
        $T = $this->getTable();

        $Columns = $T->findColumns(PDOColumn::FLAG_SEARCH);

        return array(
            new RequiredParam('search', "SEARCH for ".$T->getModelName()),
            new Param('search_by', "SEARCH by column. Allowed: [".implode(', ', array_keys($Columns))."]"),
            //$this->addField(new Field('limit', "The Number of rows to return. Max=".$Model::SEARCH_LIMIT_MAX));
            new Field('logic', "The search logic to use [AND, OR]. Default=OR"),
        );
    }


    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Search for a " . $this->getTable()->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and args
     * @throws \Exception
     * @return PDOSelect the api call response with data, message, and status
     */
    function execute(IRequest $Request) {
        $Util = new APIExecuteUtil($this);
        $Util->processRequest($Request);

        $T = $this->getTable();
        $args = $Request->getArgs();
        $search = !empty($args[0]) ? $args[0] : $Request->pluck('search');
        $search_by = $Request->pluck('search_by');
        $logic = $Request->pluck('logic') ?: 'OR';

        $Columns = $T->findColumns(PDOColumn::FLAG_SEARCH);

        $limit = $Request->pluck('limit');
        $page = $Request->pluck('page') ? : 1;

        if ($limit < 1 || $limit > $this->mLimitMax)
            $limit = $this->mLimitMax;

        if ($this->mReturnColumns) {
            if(!is_array($this->mReturnColumns))
                $this->mReturnColumns = explode(',', $this->mReturnColumns);
            $select = array_keys($T->findColumns($this->mReturnColumns));
            $Search = $T->select($select);
        } else {
            $Search = $T->search();
        }

        $Descriptor = new API_GetBrowseDescriptor($T, $Search, $this);
        $Search
            ->setDescriptor($Descriptor)
            ->limit($limit)
            ->page($page);

        if($this->mPolicy)
            $this->mPolicy->assertQueryReadAccess($Search, $T, $Request, IReadAccess::INTENT_SEARCH);

        if(strcasecmp($logic, 'OR')===0)
            $Search->setFlag(PDOWhere::LOGIC_OR);

        if($search_by && !isset($Columns[$search_by]))
            throw new \Exception("Invalid search_by column: " . implode(', ', $Columns));

        $columns = $T->findColumns($search_by ?: PDOColumn::FLAG_SEARCH);

        if(!is_int($search)) {
            $columns2 = array();
            foreach($columns as $Column)
                if(!$Column->hasFlag(PDOColumn::FLAG_NUMERIC))
                    $columns2[$Column->getName()] = $Column;
            if($columns2)
                $columns = $columns2;
        }

        if(!$columns)
            throw new \Exception("No SEARCH fields defined in ".$T->getModelName());

        if($T::SEARCH_WILDCARD) {
            if(strpos($search, '*') !== false)
                $search = str_replace('*', '%', $search);
            else
                $search .= '%';
            foreach($columns as $name=>$Column)
                $Search->where($name . ' LIKE', $search);
        } else {
            foreach($columns as $name=>$Column)
                $Search->where($name, $search);
        }

        return $Search;
    }
}