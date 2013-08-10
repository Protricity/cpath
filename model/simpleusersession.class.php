<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Model;

use CPath\Base;
use CPath\Interfaces\IUser;
use CPath\Interfaces\IUserSession;
use CPath\Interfaces\SessionDisabledException;
use CPath\Interfaces\SessionExpiredException;
use CPath\Interfaces\SessionNotActiveException;
use CPath\Log;
use CPath\Util;

class SimpleUserSession implements IUserSession {
    const SessionKey = '_session';

    private $mUserID;
    /** @var SimpleUserSession */
    private static $mSession = NULL;

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID() {
        return $this->mUserID;
    }

    // Statics

    /**
     * Loads a session instance from a session key
     * @param $key String the session key to search for
     * @return IUserSession the found user session
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    static function loadByKey($key) {
        throw new SessionExpiredException("Stored Session not implemented in " . __CLASS__);
    }

    /**
     * Loads a session instance from the active
     * @return IUserSession the found user session
     * @throws SessionNotActiveException if the session was not active
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    static function loadBySession() {
        if(static::$mSession)
            return static::$mSession;

        static::startSession();
        if(!isset($_SESSION, $_SESSION[self::SessionKey]))
            throw new SessionNotActiveException("User Session could not be found");

        $id = $_SESSION[self::SessionKey];
        $Session = new SimpleUserSession();
        $Session->mUserID = $id;
        return $Session;
    }

    /**
     * Create a new Session for an IUser Instance
     * @param int|NULL $expireInSeconds time in seconds before the session expires (or 0 for unlimited)
     * @param IUser $User
     */
    static function createNewSession(IUser $User, $expireInSeconds=NULL) {

        if(Base::isCLI())
            $_SESSION = array();
        else{
            static::startSession(true);
        }
        $_SESSION[static::SessionKey] = $User->getID();
        $Session = new SimpleUserSession();
        $Session->mUserID = $User->getID();
        self::$mSession = $Session;
    }

    /**
     * Log out of a user account
     * @return boolean true if the session was destroyed
     */
    static function destroySession() {
        try {
            $Session = static::loadBySession();
        } catch (SessionNotActiveException $ex ) {
            session_unset();
            return false ;
        }
        session_unset();
        return true ;
    }


    protected static function startSession($throwOnFail=false) {
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
}