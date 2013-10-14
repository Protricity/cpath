<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IAssignAccess;
use CPath\Model\Response;

interface IPostExecute {

    /**
     * Perform on successful API_Get execution
     * @param PDOModel $NewModel the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostExecute(PDOModel $NewModel, IRequest $Request, IResponse $Response);
}

class API_Post extends API_Base {

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupAPI() {
        $Model = $this->getModel();

        foreach($Model::findColumns($Model::INSERT ?: PDOColumn::FLAG_INSERT) as $Column)
            $Column->addToAPI($this);
    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Create a new ".$this->getModel()->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $Model = $this->getModel();

        $Policy = $this->getSecurityPolicy();

        $Policy->assignAccessID($Request, IAssignAccess::INTENT_POST);
        if($Model instanceof IAssignAccess)
            $Model->assignAccessID($Request, IAssignAccess::INTENT_POST);

        $Model = $Model::createFromArray($Request);

        $Response = new Response("Created " . $Model . " Successfully.", true, $Model);

        if($this instanceof IPostExecute)
            $Response = $this->onPostExecute($Model, $Request, $Response) ?: $Response;

        return $Response;
    }
}
