<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Describable\IDescribable;
use CPath\Framework\PDO\Templates\User\PDOUserModel;
use CPath\Framework\PDO\Templates\User\PDOUserTable;
use CPath\Handlers\Api\PasswordField;
use CPath\Handlers\API;
use CPath\Handlers\Api\RequiredParam;
use CPath\Interfaces\IRequest;
use CPath\Framework\User\Session\ISessionManager;
use CPath\Response\IResponse;
use CPath\Response\Response;

interface IPostLoginExecute {

    /**
     * Perform on successful API_Get execution
     * @param PDOUserModel $User the logged in user account instance
     * @param ISessionManager $Session the logged in user session
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostLoginExecute(PDOUserModel $User, ISessionManager $Session, IRequest $Request, IResponse $Response);
}

class API_PostUserLogin extends API_Base {
    private $mUserTable;

    /**
     * Construct an instance of this API
     * @param PDOUserTable $Table the PDOUserTable for this API
     */
    function __construct(PDOUserTable $Table) {
        parent::__construct($Table);
        $this->mUserTable = $Table;
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupFields() {
        $this->addField('name', new RequiredParam("Username or Email Address"));
        $this->addField('password', new PasswordField("Password"));
        $this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Log in as a ".$this->getTable()->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $Table = $this->mUserTable;
        $Session = $Table->login($Request['name'], $Request['password'], NULL, $User);
        $User = $Session->
        $Response = new Response("Logged in as user '".$User->getUsername()."' successfully", true, array(
            'user' => $User,
            'session' => $Session,
        ));

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPostLogoutExecute)
                $Response = $Handler->onPostLogoutExecute($User, $Session, $Request, $Response) ?: $Response;

        return $Response;
    }
}
