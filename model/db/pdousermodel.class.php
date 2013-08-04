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
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIRequiredParam;
use CPath\Handlers\SimpleAPI;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IUser;
use CPath\Interfaces\IUserSession;
use CPath\Interfaces\InvalidUserSessionException;
use CPath\Log;
use CPath\Model\Response;
use CPath\Util;

class UserNotFoundException extends \Exception {}
class IncorrectUsernameOrPasswordException extends \Exception {
    public function __construct($msg="The username, email or password was not found") {
        parent::__construct($msg);
    }
}
class PasswordsDoNotMatchException extends \Exception {
    public function __construct($msg="Please make sure the passwords match") {
        parent::__construct($msg);
    }
}

abstract class PDOUserModel extends PDOModel implements IUser {

    const FieldID = NULL;
    const FieldUsername = NULL;
    const FieldEmail = NULL;
    const FieldPassword = NULL;
    const FieldFlags = NULL;

    const CacheEnabled = true;

    const SessionClass = NULL;

    private $mFlags = 0;
    /** @var PDOUserModel[] */
    private static $mSession = array();

    public function __construct() {
        $this->mFlags = (int)$this->{static::FieldFlags};
    }

    function setFlag($flags, $commit=true, $remove=false) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $oldFlags = $this->mFlags;
        if(!$remove)
            $this->mFlags |= $oldFlags;
        else
            $this->mFlags = $oldFlags & ~$flags;
        parent::setField(static::FieldFlags, $this->mFlags, $commit);
    }

    function checkPassword($password) {
        $hash = $this->{static::FieldPassword};
        if(static::hashPassword($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException();
    }

    function changePassword($newPassword, $confirmPassword=NULL) {
        if($confirmPassword !== NULL)
            static::confirmPassword($newPassword, $confirmPassword);
        $this->setField(static::FieldPassword, static::hashPassword($newPassword), true);
    }

    /**
     * Returns true if the user is a guest account
     * @return boolean true if user is a guest account
     */
    function isGuestAccount() {
        return $this->mFlags & static::FlagGuest ? true : false;
    }

    /**
     * Returns true if the user is an admin
     * @return boolean true if user is an admin
     */
    function isAdmin() {
        return $this->mFlags & static::FlagAdmin ? true : false;
    }

    /**
     * Returns true if the user is viewing debug mode
     * @return boolean true if user is viewing debug mode
     */
    function isDebug() {
        return $this->mFlags & static::FlagDebug ? true : false;
    }

    /**
     * Return a handler to act in place of this handler
     * @return IHandler $Handler an IHandler instance
     */
    function getAggregateHandler()
    {
        $Handlers = parent::getAggregateHandler();

        $Source = $this;
        $Handlers->addHandler('POST', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
            $request = $API->processRequest($Route);
            $login = $request['login'];
            $confirm = $request[static::FieldPassword.'_confirm'];
            $pass = $request[static::FieldPassword];
            unset($request['login']);
            unset($request[static::FieldPassword.'_confirm']);
            if($confirm !== NULL)
                $Source::confirmPassword($pass, $confirm);
            $request[static::FieldPassword] = $Source::hashPassword($pass);
            /** @var PDOUserModel */
            $User = parent::createFromArray($request);
            if($login) {
                $Source::login($request[static::FieldUsername], $pass);
                return new Response("Created and logged in user '".$request[static::FieldUsername]."' successfully", true, $User);
            }
            return new Response("Created user '".$request[static::FieldUsername]."' successfully", true, $User);
        }, array(
            static::FieldUsername => new APIRequiredField("Username"),
            static::FieldEmail => new APIRequiredField("Email Address"),
            static::FieldPassword => new APIRequiredField("Password"),
            static::FieldPassword.'_confirm' => new APIField("Confirm Password"),
            'login' => new APIField("Log in after"),
        ), "Create a new ".$this->getModelName()), true);

        $Handlers->addHandler('POST login', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
            $request = $API->processRequest($Route);
            /** @var PDOUserModel $User  */
            $User = $Source::login($request['name'], $request['password']);
            return new Response("Logged in as user '".$request[static::FieldUsername]."' successfully", true, $User);
        }, array(
            'name' => new APIRequiredParam("Username or Email Address"),
            'password' => new APIRequiredParam("Password")
        ), "Log in"));

        $Handlers->addHandler('POST logout', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
            if($Source::logout())
                return new Response("Logged out successfully", true);
            return new Response("User was not logged in", false);
        }, array(), "Log out"));

        return $Handlers;
    }

    // Statics

    static function confirmPassword($newPassword, $confirmPassword) {
        if(strcmp($newPassword, $confirmPassword) !== 0)
            throw new PasswordsDoNotMatchException();
    }

    static function hashPassword($password, $oldPassword=NULL) {
        return crypt($password, $oldPassword);
    }

    /**
     * Loads a user instance from a session key
     * @param $key String the session key to search for
     * @return PDOUserModel the found user instance or primary key id of the user
     */
    static function loadBySessionKey($key)
    {
        /** @var IUserSession $class  */
        $class = static::SessionClass;
        $Session = $class::loadByKey($key);
        return static::loadByPrimaryKey($Session->getUserID());
    }

    /**
     * Loads a user instance from the active session
     * @return PDOUserModel|NULL the found user instance or null if not found
     */
    static function loadBySession() {
        /** @var IUserSession $class  */
        $class = static::SessionClass;
        return static::loadByPrimaryKey($class::loadBySession()->getUserID());
    }

    /**
     * Get the current user session or return a guest account
     * @param bool $throwOnFail throws an exception if the user session was not available
     * @return PDOUserModel|NULL the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    static function getUserSession($throwOnFail = true) {
        $class = get_called_class();
        if(isset(self::$mSession[$class]))
            return self::$mSession[$class];
        if($throwOnFail)
            return self::$mSession[$class] = static::loadBySession($throwOnFail);
        try{
            return self::$mSession[$class] = static::loadBySession($throwOnFail);
        } catch (InvalidUserSessionException $ex) {
            return false;
        }
    }

    /**
     * Gets or creates an instance of a guest user
     * @param $insertFields Array|NULL optional associative array of fields and values used when inserting guest
     * @return PDOUserModel the guest user instance
     */
    static function loadGuestAccount(Array $insertFields=array()) {
        /** @var PDOUserModel $User  */
        $User = static::searchByField(static::FieldUsername, 'guest')->fetch();
        if(!$User) {
            if(!isset($insertFields[static::FieldFlags]))
                $insertFields[static::FieldFlags] = 0;
            $insertFields[static::FieldFlags] |= static::FlagGuest;
            $User = static::createFromArray($insertFields + array(
                static::FieldUsername => 'guest',
                static::FieldEmail => 'guest@noemail.com',
            ));
        }
        $User->setFlag(static::FlagGuest);
        return $User;
    }

    /**
     * Log in to a user account
     * @param $search String the user account to search for
     * @param $password String the password to log in with
     * @param $expireInSeconds int the amount of time in seconds before an account should expire or 0 for never
     * @throws IncorrectUsernameOrPasswordException
     * @throws \Exception if the session fails to start
     * @return PDOUserModel The logged in user instance
     */
    public static function login($search, $password, $expireInSeconds=NULL) {
        /** @var PDOUserModel $User */
        $User = static::searchByFields(array(
            static::FieldUsername => $search,
            static::FieldEmail => $search,
        ))->fetch();

        if(!$User)
            throw new IncorrectUsernameOrPasswordException();

        $User->checkPassword($password);
        /** @var IUserSession $class  */
        $class = static::SessionClass;
        $class::createNewSession($User);
        return $User;
    }

    /**
     * Log out of a user account
     * @return boolean if the log out was successful
     */
    static function logout() {
        /** @var IUserSession $class  */
        $class = static::SessionClass;
        return $class::destroySession();
    }
}
