<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Table;


use CPath\Base;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\PDO\Templates\User\Model\PDOUserSessionModel;
use CPath\Framework\User\Interfaces\IUser;
use CPath\Framework\User\Session\InvalidUserSessionException;
use CPath\Framework\User\Session\ISessionManager;
use CPath\Framework\User\Session\SessionExpiredException;
use CPath\Framework\User\Session\SessionNotActiveException;
use CPath\Framework\User\Session\SessionNotFoundException;
use CPath\Log;

abstract class PDOUserSessionTable extends PDOPrimaryKeyTable implements ISessionManager {

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
     * Loads a session instance from a session key
     * @param $key String the session key to search for
     * @return ISessionManager the found user session
     * @throws SessionExpiredException if the session was active but expired or not found
     */
    function loadByKey($key) {
        $Session = $this->loadByColumns($key, static::COLUMN_KEY);
        if($c = static::COLUMN_EXPIRE) {
            $expire = $Session->$c;
            if($expire && $expire <= time())
                throw new SessionExpiredException("User Session has expired");
        }
        return $Session;
    }

    /**
     * Loads a session instance from the active
     * @return ISessionManager|PDOUserSessionModel the found user session
     * @throws SessionNotActiveException if the session was not active
     * @throws SessionExpiredException if the session was active but expired or not found
     * @throws SessionNotFoundException if the session was not found
     */
    // TODO: allow required hardware id and kill session on mismatch
    function loadBySession() {
        $class = get_called_class();
        if(!empty(self::$mSession[$class]))
            return self::$mSession[$class];

        $this->startSession();
        if(isset($_REQUEST[static::SESSION_REQUEST]))
            $key = $_REQUEST[static::SESSION_REQUEST];

        elseif(isset($_COOKIE[static::SESSION_COOKIE]))
            $key = $_COOKIE[static::SESSION_COOKIE];

        elseif(isset($_SESSION, $_SESSION[static::SESSION_KEY]))
            $key = $_SESSION[static::SESSION_KEY];

        else
            throw new SessionNotActiveException("User Session could not be found");

        try {
            $Session = $this->loadByKey($key);
        } catch (ModelNotFoundException $ex) {
            throw new SessionNotFoundException("Session {$key} not found", NULL, $ex);
        }
        self::$mSession[$class] = $Session;
        return $Session;
    }

    /**
     * Create a new Session for an IUser Instance
     * @param int|NULL $expireInSeconds time in seconds before the session expires (or 0 for unlimited)
     * @param \CPath\Framework\User\Interfaces\IUser $User
     * @return ISessionManager the new session
     */
    function createNewSession(IUser $User, $expireInSeconds=NULL) {

        $key = static::generateSessionKey();

        $expireMax = 0;
        if($e = static::SESSION_EXPIRE_DAYS)
            $expireMax = $e * 60*60*24;
        if($e = static::SESSION_EXPIRE_SECONDS)
            $expireMax = $e;
        if($expireInSeconds === NULL || $expireInSeconds > $expireMax)
            $expireInSeconds = $expireMax;

        $Session = static::createAndLoad(array(
            static::COLUMN_KEY => $key,
            static::COLUMN_USER_ID => $User->getID(),
            static::COLUMN_EXPIRE => $expireInSeconds ? time() + $expireInSeconds : 0,
        ));

        if(Base::isCLI()) {
            $_SESSION = array();
        } else {
            $this->startSession(true);
        }
        $_SESSION[static::SESSION_KEY] = $key;
        self::$mSession[get_called_class()] = $Session;
        return $Session;
    }

    /**
     * Log out of a user account
     * @return boolean true if the session was destroyed
     */
    function destroySession() {
        try {
            $Session = $this->loadBySession();
        } catch (InvalidUserSessionException $ex ) {}
        session_unset();
        if(!empty($Session)) {
            $Session->remove();
            return true;
        }
        return false ;
    }


    protected function generateSessionKey() {
        $s = '';
        for($i=0; $i<static::SESSION_KEY_LENGTH; $i++) {
            $s .= rand(1,30)%2 ? chr(rand(97,122)) : chr(rand(48,57));
        }
        return $s;
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
}
