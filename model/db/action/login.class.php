<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\Api\Action\APIAction;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IRequest;

class Action_Login extends APIAction {
    private $mUser;
    function __construct(PDOUserModel $User, ITheme $Theme=null) {
        parent::__construct($Theme);
        $this->mUser = $User;
    }

    /**
     * @return IAPI
     */
    protected function loadAPI() {
        return new API_PostUserLogin($this->mUser);
    }

    function getUser() { return $this->mUser; }

    /**
     * Filter this action according to the present circumstances
     * @return bool true if this action is available. Return not true if this action is not available
     */
    protected function isAvailable()
    {
        $Model = $this->mUser;
        if($User = $Model::loadBySession(false, false))
            return false;
        return true;
    }

    /**
     * Called when an exception occurred. This should capture exceptions that occur in ::execute and ::filter
     * @param IRequest $Request
     * @param \Exception $Ex
     * @return void
     */
    protected function onException(IRequest $Request, \Exception $Ex) {}

    /**
     * Called when a request to store the action in persistent data has been made.
     * Warning: This method may perform storage of the action in rapid succession.
     * @param IRequest $Request
     * @return void
     */
    protected function onStore(IRequest $Request) {}
}
