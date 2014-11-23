<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;

use CPath\Describable\IDescribable;
use CPath\Framework\API\Field\PasswordField;
use CPath\Framework\API\Field\RequiredParam;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\User\Session\ISessionManager;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

interface IPostLoginExecute
{

    /**
     * Perform on successful GetAPI execution
     * @param PDOUserModel $User the logged in user account inst
     * @param ISessionManager $Session the logged in user session
     * @param IRequest $Request
     * @param IResponse $Response
     * @return \CPath\Response\IResponse|null
     */
    function onPostLoginExecute(PDOUserModel $User, ISessionManager $Session, IRequest $Request, IResponse $Response);
}


class PostUserLoginAPI extends AbstractPDOAPI {
    private $mUserTable;

    /**
     * Construct an inst of this API
     * @param \CPath\Framework\PDO\Templates\User\Table\PDOUserTable $Table the PDOUserTable for this API
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
        $this->addField(new RequiredParam('name', "Username or Email Address"));
        $this->addField(new PasswordField('password', "Password"));
        //$this->generateFieldShorts();
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
     * @param IRequest $Request the IRequest inst for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final function execute(IRequest $Request, $args) {
        $Table = $this->mUserTable;
        $Response = $Table->login($Request['name'], $Request['password'], NULL, $User);
        $User = $Response['user'];
        $Session = $Response['session'];
//        $User = $Session->
//        $DataResponse = new DataResponse("Logged in as user '".$User->getUsername()."' successfully", true, array(
//            'user' => $User,
//            'session' => $Session,
//        ));

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPostLogoutExecute)
                $Response = $Handler->onPostLogoutExecute($User, $Session, $Request, $Response) ?: $Response;

        return $Response;
    }
}
