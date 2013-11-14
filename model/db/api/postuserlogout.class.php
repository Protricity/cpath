<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\SessionNotFoundException;
use CPath\Model\ExceptionResponse;
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

    protected function setupFields() {
        //$this->addField('password', new RequiredParam("Password"));
        $this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Log out";
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $User = $this->mUser;
        try {
            if(!$User::logout())
                return new Response("User was not logged in", false);
            $Response = new Response("Logged out successfully", true);
        } catch (SessionNotFoundException $ex) {
            $Response = new ExceptionResponse($ex);
        }

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPostLogoutExecute)
                $Response = $Handler->onPostLogoutExecute($User, $Request, $Response) ?: $Response;

        return $Response;
    }
}
