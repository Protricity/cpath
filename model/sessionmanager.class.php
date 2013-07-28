<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Model;

use CPath\Handlers\API;
use CPath\Handlers\APIRequiredParam;
use CPath\Handlers\APISet;
use CPath\Handlers\SimpleAPI;
use CPath\Interfaces\IUserSession;
use CPath\Log;
use CPath\Model\DB\PDOModel;
use CPath\Util;


class UserNotFoundException extends \Exception {}
class IncorrectUsernameOrPasswordException extends \Exception {
    public function __construct($msg="The username/email and or password was not found") {
        parent::__construct($msg);
    }
}
class PasswordsDoNotMatchException extends \Exception {
    public function __construct($msg="Please make sure the passwords match") {
        parent::__construct($msg);
    }
}

class UserNotLoggedInException extends \Exception {
    public function __construct($msg="User is not logged in") {
        parent::__construct($msg);
    }
}
class SessionDisabledException extends \Exception {}

class SessionManager {
    const SESSION_KEY = '_session';
    const SESSION_KEY_LENGTH = 48;

    /** @var IUserSession */
    private static $mUserSession;

    static function start($throwOnFail=false) {
        $active = false;
        switch(session_status()) {
            case PHP_SESSION_ACTIVE:
                $active = true;
                break;
            case PHP_SESSION_DISABLED:
                throw new SessionDisabledException();
                break;
            case PHP_SESSION_NONE:
                if(!headers_sent($file, $line)) {
                    session_start();
                    $active = true;
                }
                break;
        }
        if(!$active) {
            if($throwOnFail)
                throw new \Exception("Cannot Start Session: Headers already sent by {$file}:{$line}");
            Log::e(__CLASS__, "Cannot Start Session: Headers already sent by {$file}:{$line}");
        }
    }

    /**
     * Get the current user session or return a guest account
     * @param IUserSession $EmptyUser an empty user instance
     * @param bool $throwIfGuest throws an exception if the user is not logged in
     * @return IUserSession|PDOModel the user instance
     * @throws UserNotLoggedInException if the user is not logged in and $throwIfGuest==true
     */
    static function getUserSession(IUserSession $EmptyUser, $throwIfGuest=true) {
        if(!self::$mUserSession) {
            self::start();
            if(isset($_SESSION, $_SESSION[self::SESSION_KEY])) {
                $key = $_SESSION[self::SESSION_KEY];
                $User = $EmptyUser::loadFromSessionKey($key);
                if(is_scalar($User) && $User) $User = $EmptyUser::loadByPrimaryKey($User);
                if($User)
                    self::$mUserSession = $User;
                else
                    Log::e("SessionManager", "User could not be found in session");
            }
            if(!self::$mUserSession)
                self::$mUserSession = $EmptyUser::loadGuestAccount();
        }
        if($throwIfGuest && self::$mUserSession->isGuestAccount())
            throw new UserNotLoggedInException();
        return self::$mUserSession;
    }

    /**
     * Log in to a user account
     * @param IUserSession $EmptyUser an empty user instance
     * @param $search String the user account to search for
     * @param $password String the password to log in with
     * @param $expireInSeconds int the amount of time in seconds before an account should expire or 0 for never
     * @throws IncorrectUsernameOrPasswordException
     * @throws \Exception if the session fails to start
     * @return IUserSession|PDOModel The logged in user instance
     */
    public static function login(IUserSession $EmptyUser, $search, $password, $expireInSeconds=NULL) {
        /** @var IUserSession $User */
        $User = $EmptyUser::searchByAnyIndex($search)->fetch();
        if(!$User)
            throw new IncorrectUsernameOrPasswordException();
        self::checkPassword($User, $password);
        $key = self::rndstr(static::SESSION_KEY_LENGTH);

        $expireMax = 0;
        if($e = $User::SESSION_EXPIRE_DAYS)
            $expireMax = $e * 60*60*24;
        if($e = $User::SESSION_EXPIRE_SECONDS)
            $expireMax = $e;
        if(!$expireInSeconds || $expireInSeconds > $expireMax)
            $expireInSeconds = $expireMax;
        $User->storeNewSessionKey($key, $expireInSeconds ? time() + $expireInSeconds : 0);
        if(Util::isCLI())
            $_SESSION = array();
        else{
            self::start(true);
        }
        $_SESSION[self::SESSION_KEY] = $key;
        self::$mUserSession = $User;
        return $User;
    }

    /**
     * @param IUserSession $EmptyUser an empty user instance
     * @return boolean true if the user was logged in
     */
    public static function logout(IUserSession $EmptyUser) {
        $wasLoggedIn = false;
        self::start();
        if(isset($_SESSION, $_SESSION[self::SESSION_KEY]))
        {
            $key = $_SESSION[self::SESSION_KEY];
            $EmptyUser->disableSessionKey($key);
            $wasLoggedIn = true;
        }
        session_unset();
        return $wasLoggedIn ;
    }

    public static function isLoggedIn() {
        return self::$mUserSession ? true : false;
    }

    public static function checkPassword(IUserSession $User, $password) {
        $hash = $User->getPassword();
        if(self::hash($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException();
    }

    public static function changePassword(IUserSession $User, $newPassword, $confirmPassword=NULL) {
        if($confirmPassword !== NULL)
            if($newPassword != $confirmPassword)
                throw new PasswordsDoNotMatchException();
        $User->setPassword(self::hash($newPassword), true);
    }

    protected static function hash($password, $oldPassword=NULL) {
        return crypt($password, $oldPassword);
    }

    /**
     * Adds Session API calls to an APISet
     * @param IUserSession $EmptyUser an empty user instance
     * @param APISet $APISet an existing set of APIs to add to
     * @throws UserNotFoundException
     */
    public static function addAPIs(IUserSession $EmptyUser, APISet $APISet)
    {
        $APISet->addAPI('login', new SimpleAPI(function(API $API, Array $request) use ($EmptyUser) {
            $request = $API->processRequest($request);
            $User = $EmptyUser::login($request['name'], $request['password']);
            return new Response("Logged in as user '".$User->getName()."' successfully", true, $User);
        }, array(
            'name' => new APIRequiredParam("Username or Email Address"),
            'password' => new APIRequiredParam("Password")
        )));

        $APISet->addAPI('logout', new SimpleAPI(function(API $API, Array $request) use ($EmptyUser) {
            $EmptyUser::logout();
            return new Response("Logged out successfuly", true);
        }));

    }

    private static function rndstr($length=64) {
        $s = '';
        for($i=0; $i<$length; $i++) {
            $d = rand(1,30)%2;
            $s .= $d ? chr(rand(65,90)) : chr(rand(48,57));
        }
        return $s;
    }
}