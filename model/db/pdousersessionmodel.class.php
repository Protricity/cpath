<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Base;
use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIParam;
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIRequiredParam;
use CPath\Handlers\HandlerSet;
use CPath\Handlers\SimpleAPI;
use CPath\Handlers\ValidationException;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IRoutable;
use CPath\Interfaces\IRouteBuilder;
use CPath\Interfaces\IUser;
use CPath\Interfaces\IUserSession;
use CPath\Interfaces\IXML;
use CPath\Interfaces\SessionDisabledException;
use CPath\Interfaces\SessionExpiredException;
use CPath\Interfaces\SessionNotActiveException;
use CPath\Log;
use CPath\Model\Response;
use CPath\Util;



abstract class PDOUserSessionModel extends PDOModel implements IUserSession {

    const FieldKey = NULL;
    const FieldUserID = NULL;
    const FieldExpire = NULL;

    const SessionExpireDays = 365;        // The amount of time before a session should expire in days. Overwrite to change. NULL for never.
    const SessionExpireSeconds = NULL;    // The amount of time before a session should expire in seconds. Overwrite to enable.
    const SessionKey = '_session';
    const SessionKeyLength = 48;

    /** @var PDOUserSessionModel[] */
    private static $mSession = array();

    /**
     * Get the User Primary Key ID associated with this Session
     * @return String User ID
     */
    function getUserID() {
        return $this->{static::FieldUserID};
    }

    // Statics

    /**
     * Loads a session instance from a session key
     * @param $key String the session key to search for
     * @return IUserSession the found user session
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    static function loadByKey($key) {
        $Session = static::loadByColumns(static::FieldKey, $key);
        $expire = $Session->{static::FieldExpire};
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
        if(self::$mSession[$class])
            return self::$mSession[$class];

        static::startSession();
        if(isset($_SESSION, $_SESSION[self::SessionKey]))
            throw new SessionNotActiveException("User Session could not be found");

        $key = $_SESSION[self::SessionKey];
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
     */
    static function createNewSession(IUser $User, $expireInSeconds=NULL) {

        $key = static::generateSessionKey();

        $expireMax = 0;
        if($e = static::SessionExpireDays)
            $expireMax = $e * 60*60*24;
        if($e = static::SessionExpireSeconds)
            $expireMax = $e;
        if($expireInSeconds === NULL || $expireInSeconds > $expireMax)
            $expireInSeconds = $expireMax;

        $Session = static::createFromArray(array(
            static::FieldKey => $key,
            static::FieldUserID => $User->getID(),
            static::FieldExpire => $expireInSeconds ? time() + $expireInSeconds : 0,
        ));

        if(Base::isCLI())
            $_SESSION = array();
        else{
            static::startSession(true);
        }
        $_SESSION[static::SessionKey] = $key;
        self::$mSession[get_called_class()] = $Session;
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
        for($i=0; $i<static::SessionKeyLength; $i++) {
            $s .= rand(1,30)%2 ? chr(rand(97,122)) : chr(rand(48,57));
        }
        return $s;
    }


    protected static function startSession($throwOnFail=false) {
        $active = false;
        switch(session_status()) {
            case PHP_SESSION_ACTIVE:
                $active = true;
                break;
            case PHP_SESSION_DISABLED:
                throw new SessionDisabledException();
                break;
            case PHP_SESSION_NONE:
                if(headers_sent($file, $line)) {
                    if($throwOnFail)
                        throw new \Exception("Cannot Start Session: Headers already sent by {$file}:{$line}");
                    Log::e(__CLASS__, "Cannot Start Session: Headers already sent by {$file}:{$line}");
                } else {
                    session_start();
                    $active = true;
                }
                break;
        }
        return $active;
    }
}
