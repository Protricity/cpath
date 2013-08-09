<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Base;
use CPath\Exceptions\ValidationException;
use CPath\Handlers\HandlerSet;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IUser;
use CPath\Interfaces\IUserSession;
use CPath\Interfaces\InvalidUserSessionException;
use CPath\Log;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;
use CPath\Util;
use CPath\Validate;

/** Thrown if a user account was not found */
class UserNotFoundException extends \Exception {}

/** Throw when the username or password does not match */
class IncorrectUsernameOrPasswordException extends \Exception {
    public function __construct($msg="The username, email or password was not found") {
        parent::__construct($msg);
    }
}
/** Throw when the password and confirmation password do not match */
class PasswordsDoNotMatchException extends \Exception {
    public function __construct($msg="Please make sure the passwords match") {
        parent::__construct($msg);
    }
}

/**
 * Class PDOUserModel
 * A PDOModel for User Account Tables
 * Provides additional user-specific functionality and API endpoints
 * @package CPath\Model\DB
 */
abstract class PDOUserModel extends PDOModel implements IUser {

    // User-specific column
    /** (primary int) The User Account integer identifier */
    const ColumnID = NULL;
    /** (string) The username column */
    const ColumnUsername = NULL;
    /** (string) The email address column */
    const ColumnEmail = NULL;
    /** (string) The password column*/
    const ColumnPassword = NULL;
    /** (int) The account flags column */
    const ColumnFlags = NULL;

    /** Enable cache for user accounts by default */
    const CacheEnabled = true;

    /** Confirm Password by default */
    const ConfirmPassword = true;

    /** Specify the IUserSession class or model */
    const SessionClass = NULL;

    /** @var int User Account flags */
    private $mFlags = 0;

    /** @var PDOUserModel[] */
    private static $mSession = array();

    public function __construct() {
        $this->mFlags = (int)$this->{static::ColumnFlags};
    }

    public function getUsername() { return $this->{static::ColumnUsername}; }
    public function setUsername($value, $commit=true) { return $this->updateColumn(static::ColumnUsername, $value, $commit); }

    public function getEmail() { return $this->{static::ColumnEmail}; }
    public function setEmail($value, $commit=true) { return $this->updateColumn(static::ColumnEmail, $value, $commit, FILTER_VALIDATE_EMAIL); }

    function setFlag($flags, $commit=true, $remove=false) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $oldFlags = $this->mFlags;
        if(!$remove)
            $this->mFlags |= $oldFlags;
        else
            $this->mFlags = $oldFlags & ~$flags;
        $this->updateColumn(static::ColumnFlags, $this->mFlags, $commit);
    }

    function checkPassword($password) {
        $hash = $this->{static::ColumnPassword};
        if(static::hashPassword($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException();
    }

    function changePassword($newPassword, $confirmPassword=NULL) {
        if($confirmPassword !== NULL)
            static::confirmPassword($newPassword, $confirmPassword);
        $this->updateColumn(static::ColumnPassword, static::hashPassword($newPassword), true);
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
     * Update a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true) {
        if($column == static::ColumnPassword)
            $value = static::hashPassword($value);
        return parent::updateColumn($column, $value, $commit);
    }

    /**
     * Returns the default IHandlerSet collection for this PDOModel type
     * @param HandlerSet $Handlers a set of handlers to add to, otherwise a new HandlerSet is created
     * @return HandlerSet a set of common handler routes for this PDOModel type
     */
    function getDefaultHandlers(HandlerSet $Handlers=NULL) {
        if($Handlers === NULL)
            $Handlers = new HandlerSet($this);

        if(!($this instanceof IReadAccess) || !($this instanceof IWriteAccess))
            Log::e(get_class($this), get_class($this) . " does not implement IReadAccess or IWriteAccess. Users may potentially edit other user's profiles");

        $Handlers->add('GET', new API_Get($this, static::ColumnUsername));
        $Handlers->add('GET search', new API_GetSearch($this));
        $Handlers->add('POST', new API_PostUser($this));
        $Handlers->add('POST login', new API_PostUserLogin($this));
        $Handlers->add('POST logout', new API_PostUserLogout($this));
        $Handlers->add('PATCH', new API_Patch($this, static::ColumnUsername));
        //$Handlers->addHandler('DELETE', new API_Delete($this, static::ColumnUsername));

        return $Handlers;
    }

    // Statics

    /**
     * Creates a new Model based on the provided row of column value pairs
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOUserModel|null returns NULL if no primary key column is available
     * @throws ModelAlreadyExistsException
     * @throws \Exception|\PDOException
     * @throws ValidationException if a column fails to validate
     */
    static function createFromArray($row) {
        if(isset($row[static::ColumnPassword]))
            $row[static::ColumnPassword] = static::hashPassword($row[static::ColumnPassword]);
        return parent::createFromArray($row);
    }

    /**
     * Confirm two passwords match
     * @param $newPassword
     * @param $confirmPassword
     * @throws PasswordsDoNotMatchException if passwords do not match
     */
    static function confirmPassword($newPassword, $confirmPassword) {
        if(strcmp($newPassword, $confirmPassword) !== 0)
            throw new PasswordsDoNotMatchException();
    }

    /**
     * Hash passwords
     * @param $password
     * @param null $oldPassword
     * @return string
     */
    private static function hashPassword($password, $oldPassword=NULL) {
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
     * @param $insertFields Array|NULL optional associative array of columns and values used when inserting guest
     * @return PDOUserModel the guest user instance
     */
    static function loadGuestAccount(Array $insertFields=array()) {
        /** @var PDOUserModel $User  */
        $User = static::searchByColumns('guest', static::ColumnUsername)->fetch();
        if(!$User) {
            if(!isset($insertFields[static::ColumnFlags]))
                $insertFields[static::ColumnFlags] = 0;
            $insertFields[static::ColumnFlags] |= static::FlagGuest;
            $User = static::createFromArray($insertFields + array(
                static::ColumnUsername => 'guest',
                static::ColumnEmail => 'guest@noemail.com',
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
        $User = static::searchByColumns($search, array(
            static::ColumnUsername,
            static::ColumnEmail,
        ))->fetch();

        if(!$User)
            throw new IncorrectUsernameOrPasswordException();

        $User->checkPassword($password);
        /** @var IUserSession $class  */
        $class = static::SessionClass;
        $class::createNewSession($User, $expireInSeconds);
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