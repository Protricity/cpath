<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Framework\User\Session;

use CPath\Base;
use CPath\Framework\User\Interfaces\IUser;
use CPath\Log;

class SimpleSession implements ISessionManager {
    const SESSION_KEY = '_session';

    private $mUserID;
    /** @var SimpleSession */
    private static $mSession = NULL;

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID() {
        return $this->mUserID;
    }

    /**
     * Loads a session instance from a session key
     * @param $key String the session key to search for
     * @return ISessionManager the found user session
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    function loadByKey($key) {
        throw new SessionExpiredException("Stored Session not implemented in " . __CLASS__);
    }

    /**
     * Loads a session instance from the active
     * @return \CPath\Framework\User\\CPath\Framework\User\Session\ISessionManager the found user session
     * @throws SessionNotActiveException if the session was not active
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    function loadBySession() {
        if(static::$mSession)
            return static::$mSession;

        $this->startSession();
        if(!isset($_SESSION, $_SESSION[self::SESSION_KEY]))
            throw new SessionNotActiveException("User Session could not be found");

        $id = $_SESSION[self::SESSION_KEY];
        $Session = new SimpleSession();
        $Session->mUserID = $id;
        return $Session;
    }

    /**
     * Create a new Session for an IUser Instance
     * @param int|NULL $expireInSeconds time in seconds before the session expires (or 0 for unlimited)
     * @param IUser $User
     * @return ISessionManager the new session
     */
    // TODO: allow required hardware id and kill session on mismatch
    function createNewSession(IUser $User, $expireInSeconds=NULL) {

        if(Base::isCLI())
            $_SESSION = array();
        else{
            $this->startSession(true);
        }
        $_SESSION[static::SESSION_KEY] = $User->getID();
        $Session = new SimpleSession();
        $Session->mUserID = $User->getID();
        self::$mSession = $Session;
        return $Session;
    }

    /**
     * Log out of a user account
     * @return boolean true if the session was destroyed
     */
    function destroySession() {
        try {
            $this->loadBySession();
        } catch (SessionNotActiveException $ex ) {
            session_unset();
            return false ;
        }
        session_unset();
        return true ;
    }


    protected function startSession($throwOnFail=false) {
        $active = isset($_SESSION);
        if(!$active) {
            if(headers_sent($file, $line)) {
                if($throwOnFail)
                    throw new \Exception("Cannot Start Session: Headers already sent by {$file}:{$line}");
                Log::e(__CLASS__, "Cannot Start Session: Headers already sent by {$file}:{$line}");
            } else {
                session_start();
                $active = true;
            }
        }
        return $active;
    }

    // Static

    /**
     * Return the full class name via get_called_class
     * @return String the Class name
     */
    final static function cls() { return get_called_class(); }
}