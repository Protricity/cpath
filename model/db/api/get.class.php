<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIRequiredParam;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\InvalidAPIException;
use CPath\Model\DB\Interfaces\ILimitApiQuery;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\Response;

interface IGetExecute {

    /**
     * Perform on successful API_Get execution
     * @param PDOModel $Model the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onGetExecute(PDOModel $Model, IRequest $Request, IResponse $Response);
}

class API_Get extends API_Base {
    private $mSearchColumns;
    private $mColumns;
    private $mIDField;

    /**
     * Construct an instance of the GET API
     * @param PDOModel|IReadAccess $Model the user source object for this API
     * @param string|array $searchColumns a column or array of columns that may be used to search for Models.
     * PRIMARY key is already included
     */
    function __construct(PDOModel $Model, $searchColumns=NULL) {
        parent::__construct($Model);
        $this->mSearchColumns = $searchColumns ?: $Model::HANDLER_IDS ?: $Model::PRIMARY;
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    protected function setupAPI() {
        $Model = $this->getModel();
        $this->mColumns = $Model->findColumns($this->mSearchColumns);

        if(!$this->mColumns)
            throw new InvalidAPIException($Model->modelName()
                . " GET/PATCH/DELETE APIs must have a ::PRIMARY or ::HANDLER_IDS column or provide at least one alternative column");

        $keys = array_keys($this->mColumns);
        if(sizeof($keys) > 1) {
            foreach( $keys as $i => &$key)
                if($i)
                    $key = ($i == sizeof($keys) - 1 ? ' or ' : ', ') . $key;
            $this->mIDField = 'id';
        } else {
            $this->mIDField = $keys[0];
        }
        $this->addField($this->mIDField, new APIRequiredParam($Model->modelName() . ' ' . implode('', $keys)));
    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Get information about this " . $this->getModel()->modelName();
    }



    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return PDOModel|IResponse the found model which implements IResponseAggregate
     * @throws ModelNotFoundException if the Model was not found
     */
    final protected function doExecute(IRequest $Request) {

        $Model = $this->getModel();
        $this->processRequest($Request);
        $id = $Request->pluck($this->mIDField);

        /** @var PDOModelSelect $Search  */
        $Search = $Model::search();
        $Search->limit(1);
        $Search->whereSQL('(');
        $Search->setFlag(PDOWhere::LOGIC_OR);
        foreach($this->mColumns as $name => $Column)
            $Search->where($name, $id);
        $Search->unsetFlag(PDOWhere::LOGIC_OR);
        $Search->whereSQL(')');

        $Policy = $this->getSecurityPolicy();

        $Policy->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_GET);
        if($Model instanceof IReadAccess)
            $Model->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_GET);

        /** @var PDOModel$GetModel  */
        $GetModel = $Search->fetch();
        if(!$GetModel)
            throw new ModelNotFoundException($Model::modelName() . " '{$id}' was not found");

        $Policy->assertReadAccess($GetModel, $Request, IReadAccess::INTENT_GET);
        if($Model instanceof IReadAccess)
            $Model->assertReadAccess($GetModel, $Request, IReadAccess::INTENT_GET);

        $Response = $GetModel->createResponse();

        if($this instanceof IGetExecute)
            $Response = $this->onGetExecute($GetModel, $Request, $Response) ?: $Response;

        return $Response;
    }
}
