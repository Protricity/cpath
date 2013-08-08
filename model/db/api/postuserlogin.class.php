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

class API_PostUserLogin extends API {
    private $mUser;

    /**
     * Construct an instance of this API
     * @param PDOUserModel $Model the user source object for this API
     */
    function __construct(PDOUserModel $Model) {
        parent::__construct();
        $this->mUser = $Model;

        $this->addField('name', new APIRequiredParam("Username or Email Address"));
        $this->addField('password', new APIRequiredParam("Password"));
    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Log in as ".$this->mUser->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    function execute(IRequest $Request) {
        $User = $this->mUser;
        $this->processRequest($Request);
        $User = $User::login($Request['name'], $Request['password']);
        return new Response("Logged in as user '".$User->getUsername()."' successfully", true, $User);
    }
}
