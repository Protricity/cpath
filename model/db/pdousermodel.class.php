<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Actions\IActionAggregate;
use CPath\Actions\IActionManager;
use CPath\Handlers\Views\APIMultiView;
use CPath\Interfaces\InvalidUserSessionException;
use CPath\Interfaces\IUser;
use CPath\Interfaces\IUserSession;
use CPath\Route\RoutableSet;

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
abstract class PDOUserModel extends PDOPrimaryKeyModel implements IUser, IActionAggregate {

    // User-specific column
    /** (primary int) The User Account integer identifier */
    const COLUMN_ID = NULL;
    /** (string) The username column */
    const COLUMN_USERNAME = NULL;
    /** (string) The email address column */
    const COLUMN_EMAIL = NULL;
    /** (string) The password column*/
    const COLUMN_PASSWORD = NULL;
    /** (int) The account flags column */
    const COLUMN_FLAGS = NULL;

    /** Enable cache for user accounts by default */
    const CACHE_ENABLED = true;

    /** Confirm Password by default */
    const PASSWORD_CONFIRM = true;

    /** Specify the IUserSession class or model */
    const SESSION_CLASS = NULL;

    /** @var int User Account flags */
    private $mFlags = 0;

    /** @var PDOUserModel[] */
    private static $mSession = array();

    public function __construct() {
        $this->mFlags = (int)$this->{static::COLUMN_FLAGS};
    }

    public function getUsername() { return $this->{static::COLUMN_USERNAME}; }
    public function setUsername($value, $commit=true) { return $this->updateColumn(static::COLUMN_USERNAME, $value, $commit); }

    public function getEmail() { return $this->{static::COLUMN_EMAIL}; }
    public function setEmail($value, $commit=true) { return $this->updateColumn(static::COLUMN_EMAIL, $value, $commit, FILTER_VALIDATE_EMAIL); }

    function addFlags($flags, $commit=true) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("addFlags 'flags' parameter must be an integer");
        //$oldFlags = $this->mFlags;
        $this->mFlags |= $flags;
        $this->updateColumn(static::COLUMN_FLAGS, $this->mFlags, $commit);
    }

    function removeFlags($flags, $commit=true) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("removeFlags 'flags' parameter must be an integer");
        //$oldFlags = $this->mFlags;
        $this->mFlags = $this->mFlags & ~$flags;
        $this->updateColumn(static::COLUMN_FLAGS, $this->mFlags, $commit);
    }

    function checkPassword($password) {
        $hash = $this->{static::COLUMN_PASSWORD};
        if(!static::checkHash($password, $hash))
            throw new IncorrectUsernameOrPasswordException();
    }

    function changePassword($newPassword, $confirmPassword=NULL) {
        if($confirmPassword !== NULL)
            static::confirmPassword($newPassword, $confirmPassword);
        if(!$newPassword)
            throw new \InvalidArgumentException("Empty password provided");
        $this->updateColumn(static::COLUMN_PASSWORD, $newPassword, true); // It should auto hash
    }

    /**
     * Returns true if the user is a guest account
     * @return boolean true if user is a guest account
     */
    function isGuestAccount() {
        return $this->mFlags & static::FLAG_GUEST ? true : false;
    }

    /**
     * Returns true if the user is an admin
     * @return boolean true if user is an admin
     */
    function isAdmin() {
        return $this->mFlags & static::FLAG_ADMIN ? true : false;
    }

    /**
     * Returns true if the user is viewing debug mode
     * @return boolean true if user is viewing debug mode
     */
    function isDebug() {
        return $this->mFlags & static::FLAG_DEBUG ? true : false;
    }


    /**
     * UPDATE a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true) {
        if($column == static::COLUMN_PASSWORD)
            $value = static::hashPassword($value);
        return parent::updateColumn($column, $value, $commit);
    }

    /**
     * Returns the default IHandlerSet collection for this PDOModel type.
     * Note: if this method is called in a PDOModel thta does not implement IRoutable, a fatal error will occur
     * @param bool $readOnly
     * @param bool $allowDelete
     * @return RoutableSet a set of common routes for this PDOModel type
     */
    function loadDefaultRouteSet($readOnly=true, $allowDelete=false) {
        $Routes = RoutableSet::fromHandler($this);
        $Routes['GET :api'] = new APIMultiView($Routes);
        $Routes['POST :api'] = new APIMultiView($Routes);

        $Routes['GET'] = new API_Get($this);
        $Routes['GET search'] = new API_GetSearch($this);
        $Routes['GET browse'] = new API_GetBrowse($this);

        if(!$readOnly)
            $Routes['POST'] = new API_PostUser($this);
        $Routes['POST login'] = new API_PostUserLogin($this);
        $Routes['POST logout'] = new API_PostUserLogout($this);
        $Routes['POST password'] = new API_PostUserPassword($this);
        $Routes->setDefault($Routes['GET browse']);
        return $Routes;
    }

    /**
     * Load all available actions from this object.
     */
    function loadActions(IActionManager $Manager) {
        $Manager->addAction(new Action_Login($this));
    }

    // Statics

    /**
     * Internal method inserts an associative array into the database.
     * Overwritten methods must include parent::insertRow($row);
     * @param array $row
     */
    protected static function insertRow(Array $row) {
        if(isset($row[static::COLUMN_PASSWORD]))
            $row[static::COLUMN_PASSWORD] = static::hashPassword($row[static::COLUMN_PASSWORD]);
        if(!isset($row[static::COLUMN_FLAGS]))
            $row[static::COLUMN_FLAGS] = 0;
        parent::insertRow($row);
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
     * @return string
     */
    private static function hashPassword($password) {
        if(function_exists('password_hash')) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
        } else {
            if(!function_exists('mcrypt_create_iv')) {
                $salt = uniqid(null, true);
            } else {
                $salt = \mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
            }
            $salt = base64_encode($salt);
            $salt = str_replace('+', '.', $salt);
            $hash = crypt($password, '$2y$10$'.$salt.'$');
        }
        return $hash;
    }

    /**
     * Check a password against a hash
     * @param $password
     * @param null $oldPassword
     * @return bool true if hash matches
     */
    private static function checkHash($password, $oldPassword=NULL) {
        if(crypt($password, $oldPassword) == $oldPassword)
            return true;
        return false;
    }

    /**
     * Loads a user instance from a session key
     * @param $key String the session key to search for
     * @return PDOUserModel the found user instance or primary key id of the user
     */
    static function loadBySessionKey($key)
    {
        /** @var IUserSession $class  */
        $class = static::SESSION_CLASS;
        $Session = $class::loadByKey($key);
        return static::loadByPrimaryKey($Session->getUserID());
    }


    /**
     * Load or get the current user session
     * @return IUserSession the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    static function loadSession() {
        /** @var IUserSession $class  */
        $class = static::SESSION_CLASS;
        return $class::loadBySession();
    }


    /**
     * Get the current user session or return a guest account
     * @param bool $throwOnFail throws an exception if the user session was not available
     * @param bool $allowGuest returns a guest account if no session is available
     * @return PDOUserModel|NULL the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    static function loadBySession($throwOnFail = true, $allowGuest = false) {
        $class = get_called_class();
        if(isset(self::$mSession[$class]))
            return self::$mSession[$class];
        if($throwOnFail && !$allowGuest)
            return self::$mSession[$class] = static::loadByPrimaryKey(static::loadSession($throwOnFail, $allowGuest)->getUserID());
        try {
            return self::$mSession[$class] = static::loadByPrimaryKey(static::loadSession($throwOnFail, $allowGuest)->getUserID());
        } catch (InvalidUserSessionException $ex) {
            if($allowGuest)
                return static::loadGuestAccount();
            if($throwOnFail)
                throw $ex;
            return NULL;
        }
    }

    /**
     * Gets or creates an instance of a guest user
     * @param $insertFields Array|NULL optional associative array of columns and values used when inserting guest
     * @return PDOUserModel the guest user instance
     */
    static function loadGuestAccount(Array $insertFields=array()) {
        /** @var PDOUserModel $User  */
        $User = static::searchByColumns('guest', static::COLUMN_USERNAME)->fetch();
        if(!$User) {
            if(!isset($insertFields[static::COLUMN_FLAGS]))
                $insertFields[static::COLUMN_FLAGS] = 0;
            $insertFields[static::COLUMN_FLAGS] |= static::FLAG_GUEST;
            $User = static::createAndLoad($insertFields + array(
                static::COLUMN_USERNAME => 'guest',
                static::COLUMN_EMAIL => 'guest@noemail.com',
            ));
        }
        $User->addFlags(static::FLAG_GUEST);
        return $User;
    }

    /**
     * Log in to a user account
     * @param String $search the user account to search for
     * @param String $password the password to log in with
     * @param int $expireInSeconds the amount of time in seconds before an account should expire or 0 for never
     * @param PDOUserModel $User the user instance loaded during login
     * @throws IncorrectUsernameOrPasswordException
     * @return IUserSession The new user session
     */
    public static function login($search, $password, $expireInSeconds=NULL, PDOUserModel &$User=NULL) {
        /** @var PDOUserModel $User */
        $User = static::searchByColumns($search, array(
            static::COLUMN_USERNAME,
            static::COLUMN_EMAIL,
        ))->fetch();

        if(!$User)
            throw new IncorrectUsernameOrPasswordException();

        $User->checkPassword($password);
        /** @var IUserSession $class  */
        $class = static::SESSION_CLASS;
        $Session = $class::createNewSession($User, $expireInSeconds);
        return $Session;
    }

    /**
     * Log out of a user account
     * @return boolean if the log out was successful
     */
    static function logout() {
        /** @var IUserSession $class  */
        $class = static::SESSION_CLASS;
        return $class::destroySession();
    }
}