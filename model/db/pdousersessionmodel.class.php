<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Base;
use CPath\Interfaces\IUser;
use CPath\Interfaces\IUserSession;
use CPath\Interfaces\SessionDisabledException;
use CPath\Interfaces\SessionExpiredException;
use CPath\Interfaces\SessionNotActiveException;
use CPath\Log;
use CPath\Model\Response;
use CPath\Util;

abstract class PDOUserSessionModel extends PDOModel implements IUserSession {

    const COLUMN_KEY = NULL;
    const COLUMN_USER_ID = NULL;
    const COLUMN_EXPIRE = NULL;

    const SESSION_EXPIRE_DAYS = 365;        // The amount of time before a session should expire in days. Overwrite to change. NULL for never.
    const SESSION_EXPIRE_SECONDS = NULL;    // The amount of time before a session should expire in seconds. Overwrite to enable.
    const SESSION_KEY = '_session';
    const SESSION_COOKIE = 'cpath_session';
    const SESSION_REQUEST = 'cpath_session';
    const SESSION_KEY_LENGTH = 48;

    /** @var PDOUserSessionModel[] */
    private static $mSession = array();

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID() {
        return $this->{static::COLUMN_USER_ID};
    }

    // Statics

    /**
     * Loads a session instance from a session key
     * @param $key String the session key to search for
     * @return IUserSession the found user session
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    static function loadByKey($key) {
        $Session = static::loadByColumns($key, static::COLUMN_KEY);
        $expire = $Session->{static::COLUMN_EXPIRE};
        if($expire && $expire <= time())
            throw new SessionExpiredException("User Session has expired");
        return $Session;
    }

    /**
     * Loads a session instance from the active
     * @return IUserSession the found user session
     * @throws SessionNotActiveException if the session was not active
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    static function loadBySession() {
        $class = get_called_class();
        if(!empty(self::$mSession[$class]))
            return self::$mSession[$class];

        static::startSession();
        if(isset($_REQUEST[self::SESSION_REQUEST]))
            $key = $_REQUEST[self::SESSION_REQUEST];

        elseif(isset($_COOKIE[self::SESSION_COOKIE]))
            $key = $_COOKIE[self::SESSION_COOKIE];

        elseif(isset($_SESSION, $_SESSION[self::SESSION_KEY]))
            $key = $_SESSION[self::SESSION_KEY];

        else
            throw new SessionNotActiveException("User Session could not be found");

        $Session = static::loadByKey($key);
        if($Session)
            self::$mSession[$class] = $Session;
        else
            throw new SessionExpiredException("User Session has expired");

        self::$mSession[$class] = $Session;
        return $Session;
    }

    /**
     * Create a new Session for an IUser Instance
     * @param int|NULL $expireInSeconds time in seconds before the session expires (or 0 for unlimited)
     * @param IUser $User
     * @return IUserSession the new session
     */
    static function createNewSession(IUser $User, $expireInSeconds=NULL) {

        $key = static::generateSessionKey();

        $expireMax = 0;
        if($e = static::SESSION_EXPIRE_DAYS)
            $expireMax = $e * 60*60*24;
        if($e = static::SESSION_EXPIRE_SECONDS)
            $expireMax = $e;
        if($expireInSeconds === NULL || $expireInSeconds > $expireMax)
            $expireInSeconds = $expireMax;

        $Session = static::createFromArray(array(
            static::COLUMN_KEY => $key,
            static::COLUMN_USER_ID => $User->getID(),
            static::COLUMN_EXPIRE => $expireInSeconds ? time() + $expireInSeconds : 0,
        ));

        if(Base::isCLI())
            $_SESSION = array();
        else{
            static::startSession(true);
        }
        $_SESSION[static::SESSION_KEY] = $key;
        self::$mSession[get_called_class()] = $Session;
        return $Session;
    }

    /**
     * Log out of a user account
     * @return boolean true if the session was destroyed
     */
    static function destroySession() {
        try {
            $Session = static::loadBySession();
        } catch (SessionNotActiveException $ex ) {}
        session_unset();
        if(!empty($Session)) {
            static::removeModel($Session);
            return true;
        }
        return false ;
    }


    protected static function generateSessionKey() {
        $s = '';
        for($i=0; $i<static::SESSION_KEY_LENGTH; $i++) {
            $s .= rand(1,30)%2 ? chr(rand(97,122)) : chr(rand(48,57));
        }
        return $s;
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
