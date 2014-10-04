<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Session;

use CPath\Framework\Response\Interfaces\IResponseAggregate;
use CPath\Framework\User\Interfaces\IUser;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;

interface ISessionManager {

    /**
     * Create a new Session for an IUser Instance
     * @param int|NULL $expireInSeconds time in seconds before the session expires (or 0 for unlimited)
     * @param \CPath\Framework\User\Interfaces\IUser $User
     * @return ISession the new session
     */
    function createNewSession(IUser $User, $expireInSeconds=NULL);

    /**
     * Loads a session instance from a session key
     * @param $key String the session key to search for
     * @return ISession the found user session
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    function loadByKey($key);

    /**
     * Loads an existing session instance
     * @return ISession the found user session
     * @throws SessionNotActiveException if the session was not active
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    function loadBySession();

    /**
     * Log out of a user account
     * @return boolean true if the session was destroyed
     */
    function destroySession();
}




class InvalidUserSessionException extends \Exception implements IResponseAggregate {
    public function __construct($msg="User is not logged in") {
        parent::__construct($msg);
    }

    /**
     * @return IResponse
     */
    function createResponse() {
        $Response = new ExceptionResponse($this);
        $Response->setStatusCode(401);
        return $Response;
    }
}
class SessionDisabledException extends InvalidUserSessionException {}
class SessionNotActiveException extends InvalidUserSessionException {}
class SessionNotFoundException extends InvalidUserSessionException {}
class SessionExpiredException extends InvalidUserSessionException {}
