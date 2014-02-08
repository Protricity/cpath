<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Framework\Api\Field\RequiredParam;
use CPath\Framework\Api\Interfaces\InvalidAPIException;
use CPath\Framework\PDO\Interfaces\IAPIGetCallbacks;
use CPath\Framework\PDO\Interfaces\IPDOModelRender;
use CPath\Framework\PDO\Interfaces\IReadAccess;
use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Model\Query\PDOModelSelect;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\ModelNotFoundException;
use CPath\Framework\PDO\Table\PDOPrimaryKeyTable;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Response\IResponse;

class API_Get extends API_Base {
    private $mSearchColumns;
    private $mColumns;
    private $mIDField;

    /**
     * Construct an instance of the GET API
     * @param PDOPrimaryKeyTable|IReadAccess $Table the table instance
     * @param string|array $searchColumns a column or array of columns that may be used to search for Models.
     * PRIMARY key is already included
     */
    function __construct(PDOPrimaryKeyTable $Table, $searchColumns=NULL) {
        parent::__construct($Table);
        $this->mSearchColumns = $searchColumns ?: $Table::COLUMN_ID ?: $Table::COLUMN_PRIMARY;
    }

    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Get information about this " . $this->getTable()->getModelName();
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    final protected function setupFields() {
        $T = $this->getTable();
        $this->mColumns = $T->findColumns($this->mSearchColumns);

        if(!$this->mColumns)
            throw new InvalidAPIException($T->getModelName()
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
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return PDOPrimaryKeyModel|IResponse the found model which implements IResponseAggregate
     * @throws ModelNotFoundException if the Model was not found
     */
    final protected function doExecute(IRequest $Request) {

        $T = $this->getTable();
        $id = $Request->pluck($this->mIDField);

        /** @var PDOModelSelect $Search  */
        $Search = $T->search();
        $Search->limit(1);
        $Search->whereSQL('(');
        $Search->setFlag(PDOWhere::LOGIC_OR);
        foreach($this->mColumns as $name => $Column)
            $Search->where($name, $id);
        $Search->unsetFlag(PDOWhere::LOGIC_OR);
        $Search->whereSQL(')');

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IReadAccess)
                $Handler->assertQueryReadAccess($Search, $T, $Request, IReadAccess::INTENT_GET);

        /** @var PDOPrimaryKeyModel $GetModel  */
        $GetModel = $Search->fetch();
        if(!$GetModel)
            throw new ModelNotFoundException($T->getModelName() . " '{$id}' was not found");

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IReadAccess)
                $Handler->assertReadAccess($GetModel, $Request, IReadAccess::INTENT_GET);

        $Response = $GetModel->createResponse();

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIGetCallbacks)
                $Response = $Handler->onGetExecute($GetModel, $Request, $Response) ?: $Response;

        return $Response;
    }


    /**
     * Sends headers, executes the request, and renders an IResponse as HTML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderHTML(IRequest $Request) {

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPDOModelRender)
            {
                try {
                    $Model = $this->executeOrThrow($Request)->getDataPath();
                    $Handler->renderModel($Model, $Request);
                    return;
                } catch (\Exception $ex) {
                    $Handler->renderException($ex, $Request);
                    return;
                }
            }

        parent::renderHTML($Request);
    }
}
