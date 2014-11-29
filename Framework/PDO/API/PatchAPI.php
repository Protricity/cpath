<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;

use CPath\Data\Describable\IDescribable;
use CPath\Framework\API\Exceptions\APIException;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Field\RequiredParam;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\API\Util\APIExecuteUtil;
use CPath\Framework\PDO\Interfaces\IReadAccess;
use CPath\Framework\PDO\Interfaces\IWriteAccess;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Response\PDOModelResponse;
use CPath\Framework\PDO\Table\Column\Builders\Interfaces\IPDOColumnBuilder;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

class PatchAPI implements IAPI {
    private $mSearchColumns;
    private $mColumns;
    private $mIDField;

    private $mTable;
    private $mPolicy;

    /**
     * Construct an inst of the GET API
     * @param PDOPrimaryKeyTable $Table the table inst
     * @param string|array $searchColumns a column or array of columns that may be used to search for Models.
     * Note: PRIMARY key is already included
     * @param IWriteAccess $SecurityPolicy
     */
    function __construct(PDOPrimaryKeyTable $Table, $searchColumns=NULL, IWriteAccess $SecurityPolicy=null) {
        $this->mTable = $Table;
        $this->mSearchColumns = $searchColumns ?: $Table::COLUMN_ID ?: $Table::COLUMN_PRIMARY;
        $this->mPolicy = $SecurityPolicy;
    }

    /**
     * @return IReadAccess|PDOPrimaryKeyTable
     */
    function getTable() {
        return $this->mTable;
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Update a ".$this->getTable()->getModelName();
    }


    /**
     * Get all API Fields
     * @param IRequest $Request the IRequest inst for this render which contains the request and args
     * @throws \CPath\Framework\API\Exceptions\APIException
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request) {
        $T = $this->getTable();
        $this->mColumns = $T->findColumns($this->mSearchColumns);

        if(!$this->mColumns)
            throw new APIException($T->getModelName()
                . __CLASS__ . " APIs must have a ::PRIMARY or ::COLUMN_ID column or provide at least one alternative column");

        $keys = array_keys($this->mColumns);
        if(sizeof($keys) > 1) {
            foreach( $keys as $i => &$key)
                if($i)
                    $key = ($i == sizeof($keys) - 1 ? ' or ' : ', ') . $key;
            $this->mIDField = 'id';
        } else {
            $this->mIDField = $keys[0];
        }

        $Fields = array (
            $this->mIDField => new RequiredParam($this->mIDField, $T->getModelName() . ' ' . implode('', $keys)),
        );

        foreach($T->findColumns(PDOColumn::FLAG_UPDATE) as $Column)
            /** @var IPDOColumnBuilder $Column */
            if(!isset($fields[$Column->getName()]))
                $Fields[$Column->getName()] = $Column->generateAPIField();

        return $Fields;
    }


    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest inst for this render which contains the request and args
     * @throws \CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException
     * @throws \CPath\Framework\API\Exceptions\APIException
     * @return IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request)
    {
        $Util = new APIExecuteUtil($this);
        $Util->processRequest($Request);

        $T = $this->getTable();
        $id = $Request->pluck($this->mIDField);

        $Search = $T->search();
        $Search->limit(1);
        $Search->whereSQL('(');
        $Search->setFlag(PDOWhere::LOGIC_OR);
        /** @var PDOColumn $Column */
        foreach($this->mColumns as $name => $Column) {
            if($Column->hasFlag(PDOColumn::FLAG_NUMERIC) && !is_numeric($id))
                throw new APIException("Search value was non-numeric on numeric column '" . $name . "'");
            $Search->where($name, $id);
        }
        $Search->unsetFlag(PDOWhere::LOGIC_OR);
        $Search->whereSQL(')');

        if($this->mPolicy instanceof IReadAccess)
            $this->mPolicy->assertQueryReadAccess($Search, $T, $Request, IReadAccess::INTENT_GET);

        /** @var PDOPrimaryKeyModel $PatchModel  */
        $PatchModel = $Search->fetch();
        if(!$PatchModel)
            throw new ModelNotFoundException($T, $id);

        if($this->mPolicy instanceof IReadAccess)
            $this->mPolicy->assertReadAccess($PatchModel, $Request, IReadAccess::INTENT_GET);

        if($this->mPolicy)
            $this->mPolicy->assertWriteAccess($PatchModel, $Request, IWriteAccess::INTENT_PATCH);

        foreach($Request as $column => $value)
            if($value !== NULL)
                $PatchModel->updateColumn($column, $value, false);

        $c = $PatchModel->commitColumns();

        if(!$c)
            return new PDOModelResponse($PatchModel, "No columns were updated for {$PatchModel}.");
        return new PDOModelResponse($PatchModel, "Updated {$c} Field(s) for {$PatchModel}.");
    }
}
