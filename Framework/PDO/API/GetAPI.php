<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;


use CPath\Framework\API\Exceptions\APIException;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Field\RequiredParam;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\PDO\Interfaces\IAPIGetCallbacks;
use CPath\Framework\PDO\Interfaces\IPDOModelRender;
use CPath\Framework\PDO\Interfaces\IReadAccess;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

class GetAPI implements IAPI {
    private $mSearchColumns;
    private $mColumns;
    private $mIDField;

    private $mTable;

    /**
     * Construct an inst of the GET API
     * @param PDOPrimaryKeyTable|IReadAccess $Table the table inst
     * @param string|array $searchColumns a column or array of columns that may be used to search for Models.
     * PRIMARY key is already included
     */
    function __construct(PDOPrimaryKeyTable $Table, $searchColumns=NULL) {
        $this->mTable = $Table;
        $this->mSearchColumns = $searchColumns ?: $Table::COLUMN_ID ?: $Table::COLUMN_PRIMARY;
    }

    function getTable() {
        return $this->mTable;
    }

    /**
     * Get the Object Description
     * @return \CPath\Data\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Get information about this " . $this->getTable()->getModelName();
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
                . " GET/PATCH/DELETE APIs must have a ::PRIMARY or ::COLUMN_ID column or provide at least one alternative column");

        $keys = array_keys($this->mColumns);
        if(sizeof($keys) > 1) {
            foreach( $keys as $i => &$key)
                if($i)
                    $key = ($i == sizeof($keys) - 1 ? ' or ' : ', ') . $key;
            $this->mIDField = 'id';
        } else {
            $this->mIDField = $keys[0];
        }

        $fields = array();
        $fields[$this->mIDField] = new RequiredParam($T->getModelName() . ' ' . implode('', $keys));

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIGetCallbacks)
                $fields = $Handler->prepareGetFields($fields) ?: $fields;

        $this->addFields($fields);
    }


    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and args
     * @throws \CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException
     * @internal param Array $args additional arguments for this execution
     * @return PDOPrimaryKeyModel the found model which implements IResponseAggregate
     */
    final function execute(IRequest $Request) {
        $T = $this->getTable();
        $id = $Request->pluck($this->mIDField);

        $Search = $T->search();
        $Search->limit(1);
        $Search->whereSQL('(');
        $Search->setFlag(PDOWhere::LOGIC_OR);
        foreach($this->mColumns as $name => $Column)
            $Search->where($name, $id);
        $Search->unsetFlag(PDOWhere::LOGIC_OR);
        $Search->whereSQL(')');

        if($this->mPolicy)
            $this->mPolicy->assertQueryReadAccess($Search, $T, $Request, IReadAccess::INTENT_GET);

        /** @var PDOPrimaryKeyModel $GetModel  */
        $GetModel = $Search->fetch();
        if(!$GetModel)
            throw new ModelNotFoundException($T, $id);

        return $GetModel;
    }


    /**
     * Render request as html and sends headers as necessary
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null)  {

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPDOModelRender)
            {
                try {
                    $Model = $this
                        ->execute($Request)
                        ->getModel();
                    $Handler->renderModel($Model, $Request);
                    return;
                } catch (\Exception $ex) {
                    $Handler->renderException($ex, $Request);
                    return;
                }
            }

        $Util = new APIRenderUtil($this);
        $Util->renderHTML($Request, $Attr);
    }
}
