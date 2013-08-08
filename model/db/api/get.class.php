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
use CPath\Model\Response;

class API_Get extends API {
    private $mModel;

    /**
     * Construct an instance of this API
     * @param PDOModel $Model the user source object for this API
     */
    function __construct(PDOModel $Model) {
        parent::__construct();
        $this->mModel = $Model;

        //$this->addField('id', new APIRequiredParam($Model->getModelName() . ' ' . static::$_columns[static::Primary][1]),
    }

    /**
     * Overwrite to modify IRequest before insert
     * @param IRequest $Request the request to modify
     */
    function beforeInsert(IRequest $Request) {}

    /**
     * Overwrite to manage IRequest or PDOModel after insert
     * @param PDOModel $NewModel the newly inserted model
     * @param IRequest $Request the request that was used
     */
    function afterInsert(PDOModel $NewModel, IRequest $Request) {}

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Create a new ".$this->mModel->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    function execute(IRequest $Request) {
        $Model = $this->mModel;
        $this->processRequest($Request);
        $Model::validateRequest($Request);

        $this->beforeInsert($Request);

        $Model = $Model::createFromArray($Request);

        $this->afterInsert($Model, $Request);

        $id = '';
        if($column = $Model::Primary)
            $id = " '" . $Model->$column . "'";
        return new Response("Created " . $Model::getModelName() . "{$id} Successfully.", true, $Model);
    }
}
