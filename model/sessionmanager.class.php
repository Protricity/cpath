<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Model;

use CPath\Handlers\Api;
use CPath\Handlers\ApiParam;
use CPath\Handlers\ApiSet;
use CPath\Handlers\SimpleApi;
use CPath\Interfaces\IUserSession;
use CPath\Model\DB\PDOModel;


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

class SessionManager {
    const SESSION_KEY = '_session';
    const SESSION_KEY_LENGTH = 48;

    const FLAG_VALIDATED = 0x02;
    const FLAG_DISABLED = 0x04;

    const FLAG_DEBUG = 0x10;
    const FLAG_MANAGER = 0x20;
    const FLAG_ADMIN = 0x40;

    /** @var IUserSession */
    private static $mUserSession;

    /**
     * Get the current user session or return a guest account
     * @param IUserSession $EmptyUser an empty user instance
     * @return IUserSession|PDOModel the user instance
     */
    static function getUserSession(IUserSession $EmptyUser) {
        if(self::$mUserSession)
            return self::$mUserSession;
        if(isset($_SESSION, $_SESSION[self::SESSION_KEY]))
        {
            $key = $_SESSION[self::SESSION_KEY];
            $User = $EmptyUser::loadFromSessionKey($key);
            if(is_scalar($User)) $User = $EmptyUser::loadByPrimaryKey($User);
            return self::$mUserSession = $User;
        }
        return self::$mUserSession = $EmptyUser::loadGuestAccount();
    }

    /**
     * Log in to a user account
     * @param IUserSession $EmptyUser an empty user instance
     * @param $search String the user account to search for
     * @param $password String the password to log in with
     * @throws IncorrectUsernameOrPasswordException
     * @return IUserSession|PDOModel The logged in user instance
     */
    public static function login(IUserSession $EmptyUser, $search, $password) {
        $User = $EmptyUser::searchByAnyIndex($search)->fetch();
        if(!$User)
            throw new IncorrectUsernameOrPasswordException();
        self::checkPassword($User, $password);
        $key = openssl_random_pseudo_bytes(static::SESSION_KEY_LENGTH);
        $User->storeNewSessionKey($key, $User->getID());
        session_start();
        $_SESSION[self::SESSION_KEY] = $key;
        self::$mUserSession = $User;
        return $User;
    }

    /**
     * @param IUserSession $EmptyUser an empty user instance
     */
    public static function logout(IUserSession $EmptyUser) {

        if(isset($_SESSION, $_SESSION[self::SESSION_KEY]))
        {
            $key = $_SESSION[self::SESSION_KEY];
            $EmptyUser->disableSessionKey($key);
        }
        session_unset();
    }

    public static function isLoggedIn() {
        return self::$mUserSession ? true : false;
    }

    public static function isFlag(IUserSession $User, $flags) {
        return (int)$User->getFlags() & $flags ? true : false;
    }

    /**
     * Returns true if the user is an admin
     * @param IUserSession $User
     * @return boolean true if user is an admin
     */
    public static function isAdmin(IUserSession $User) {
        return self::isFlag($User, self::FLAG_ADMIN);
    }

    /**
     * Returns true if the user is viewing debug mode
     * @param IUserSession $User
     * @return boolean true if user is viewing debug mode
     */
    public static function isDebug(IUserSession $User) {
        return self::isFlag($User, self::FLAG_DEBUG);
    }

    public static function checkPassword(IUserSession $User, $password) {
        $hash = $User->getPassword();
        if(self::hash($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException();
    }

    public static function setFlags(IUserSession $User, $flags, $commit=true, $remove=false) {
        $oldFlags = (int)$User->getFlags();
        if(!$remove)
            $flags |= $oldFlags;
        else
            $flags = $oldFlags & ~$flags;
        $User->setFlags($flags, $commit);
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
     * Adds Session Api calls to an ApiSet
     * @param IUserSession $EmptyUser an empty user instance
     * @param ApiSet $ApiSet an existing set of Apis to add to
     * @throws UserNotFoundException
     */
    public static function addApis(IUserSession $EmptyUser, ApiSet $ApiSet)
    {
        $ApiSet->addApi('login', new SimpleApi(function(Api $API, Array $request) use ($EmptyUser) {
            $request = $API->processRequest($request);
            $User = $EmptyUser::login($request['name'], $request['password']);
            return new Response("Logged in as user '".$User->getName()."' successfully", true);
        }, array(
            'name' => new ApiParam("Username or Email Address"),
            'password' => new ApiParam("Password")
        )));

        $ApiSet->addApi('logout', new SimpleApi(function(Api $API, Array $request) use ($EmptyUser) {
            $EmptyUser::logout();
            return new Response("Logged out successfuly", true);
        }));

    }
}