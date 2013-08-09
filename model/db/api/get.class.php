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
use CPath\Interfaces\InvalidAPIException;
use CPath\Model\DB\Interfaces\ILimitApiQuery;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\Response;

class API_Get extends API_Base {
    private $mColumns;

    /**
     * Construct an instance of the GET API
     * @param PDOModel|IReadAccess $Model the user source object for this API
     * @param string|array $searchColumns a column or array of columns that may be used to search for Models.
     * Primary key is already included
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    function __construct(PDOModel $Model, $searchColumns=NULL) {
        parent::__construct($Model);

        $searchColumns = $searchColumns ?: $Model::HandlerIDColumn ?: $Model::Primary;
        $this->mColumns = $Model->findColumns($searchColumns);

        if(!$this->mColumns)
            throw new InvalidAPIException($Model->getModelName()
                . " GET/PATCH/DELETE APIs must have a ::Primary or ::HandlerIDColumn column or provide at least one alternative column");

        $keys = array_keys($this->mColumns);
        foreach( $keys as $i => &$key)
            if($i)
                $key = ($i == sizeof($keys) - 1 ? ' or ' : ', ') . $key;
        $this->addField('id', new APIRequiredParam($Model->getModelName() . implode('', $keys)));
    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Get information about this " . $this->getModel()->getModelName();
    }



    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return PDOModel|IResponse the found model which implements IResponseAggregate
     * @throws ModelNotFoundException if the Model was not found
     */
    function execute(IRequest $Request) {

        $Model = $this->getModel();
        $this->processRequest($Request);
        $id = $Request->pluck('id');

        /** @var PDOModelSelect $Search  */
        $Search = $Model::search();
        $Search->limit(1);
        $Search->where('(');
        $Search->setFlag(PDOWhere::DefaultLogicOR);
        foreach($this->mColumns as $column)
            $Search->where($column, $id);
        $Search->unsetFlag(PDOWhere::DefaultLogicOR);
        $Search->where(')');

        if($Model instanceof IReadAccess)
            $Model->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_GET);

        $GetModel = $Search->fetch();
        if(!$GetModel)
            throw new ModelNotFoundException($Model::getModelName() . " '{$id}' was not found");

        if($GetModel instanceof IReadAccess)
            $GetModel->assertReadAccess($Request, IReadAccess::INTENT_GET);

        return $GetModel;
    }
}
