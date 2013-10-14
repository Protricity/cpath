<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

use CPath\Model\ExceptionResponse;

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

interface IUserSession {

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID();

    // Statics

    /**
     * Create a new Session for an IUser Instance
     * @param int|NULL $expireInSeconds time in seconds before the session expires (or 0 for unlimited)
     * @param IUser $User
     * @return IUserSession the new session
     */
    static function createNewSession(IUser $User, $expireInSeconds=NULL);

    /**
     * Loads a session instance from a session key
     * @param $key String the session key to search for
     * @return IUserSession the found user session
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    static function loadByKey($key);

    /**
     * Loads a session instance from the active
     * @return IUserSession the found user session
     * @throws SessionNotActiveException if the session was not active
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    static function loadBySession();

    /**
     * Log out of a user account
     * @return boolean true if the session was destroyed
     */
    static function destroySession();


}