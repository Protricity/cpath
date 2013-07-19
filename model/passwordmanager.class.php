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
use CPath\Handlers\HandlerSet;
use CPath\Handlers\SimpleApi;
use CPath\Interfaces\IUserSession;
use CPath\Model\DB\ModelAlreadyExistsException;
use CPath\Model\DB\ModelNotFoundException;
use CPath\Model\DB\PDOModel;

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

class PasswordManager {
    const SESSION_KEY = '_session';
    const SESSION_KEY_LENGTH = 48;

    const FLAG_VALIDATED = 0x02;
    const FLAG_DISABLED = 0x04;

    const FLAG_DEBUG = 0x10;
    const FLAG_MANAGER = 0x20;
    const FLAG_ADMIN = 0x40;

    /** @var IUserSession */
    private static $mUserClass;
    private static $mIsLoggedIn = false;

    public static function login(IUserSession $User, $password) {
        self::checkPassword($User, $password);
        $key = openssl_random_pseudo_bytes(static::SESSION_KEY_LENGTH);
        $User->storeNewSessionKey($key, $User->getID());
        session_start();
        $_SESSION[self::SESSION_KEY] = $key;
        self::$mIsLoggedIn = true;
    }

    public static function isLoggedIn() {
        return self::$mIsLoggedIn;
    }

    public static function isFlag(IUserSession $User, $flags) {
        return (int)$User->getFlags() & $flags ? true : false;
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

    public static function checkForSessionKey() {
        if(!isset($_SESSION, $_SESSION[self::SESSION_KEY]))
            return false;
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * @param HandlerSet $Handlers
     * @param String|null $UserClass
     * @throws UserNotFoundException when user is not found
     */
    public static function addHandlers(HandlerSet $Handlers, $UserClass)
    {
        $Handlers->addHandler('login', new SimpleApi(function(Api $API, Array $request) use ($UserClass) {
            $request = $API->processRequest($request);
            /** @var IUserSession $UserClass */
            $User = $UserClass::login($request['name'], $request['password']);
            if(!$User)
                throw new UserNotFoundException("User '".$request['name'].'" not found');
            return $User;
        }, array(
            'name' => new ApiParam("Username or Email Address"),
            'password' => new ApiParam("Password")
        )));

    }
}