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

interface IPostLogoutExecute {

    /**
     * Perform on successful API_Get execution
     * @param PDOUserModel $User the logged out user account instance
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostLogoutExecute(PDOUserModel $User, IRequest $Request, IResponse $Response);
}

class API_PostUserLogout extends API_Base {
    private $mUser;

    /**
     * Construct an instance of this API
     * @param PDOUserModel $Model the user source object for this API
     */
    function __construct(PDOUserModel $Model) {
        parent::__construct($Model);
        $this->mUser = $Model;
    }

    protected function setupAPI() {}

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Log out";
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $User = $this->mUser;
        $this->processRequest($Request);
        if(!$User::logout())
            return new Response("User was not logged in", false);

        $Response = new Response("Logged out successfully", true);

        if($this instanceof IPostLogoutExecute)
            $Response = $this->onPostLogoutExecute($User, $Request, $Response) ?: $Response;

        return $Response;
    }
}
