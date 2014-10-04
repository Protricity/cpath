<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Table;

use CPath\Framework\PDO\API\GetAPI;
use CPath\Framework\PDO\API\GetBrowseAPI;
use CPath\Framework\PDO\API\GetSearchAPI;
use CPath\Framework\PDO\API\PostUserAPI;
use CPath\Framework\PDO\API\PostUserLoginAPI;
use CPath\Framework\PDO\API\PostUserLogoutAPI;
use CPath\Framework\PDO\API\PostUserPasswordAPI;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\User\Interfaces\IUser;
use CPath\Framework\User\Role\Exceptions\AuthenticationException;
use CPath\Framework\User\Role\Exceptions\PasswordMatchException;
use CPath\Framework\User\Session\InvalidUserSessionException;
use CPath\Framework\User\Session\ISessionManager;
use CPath\Route\RoutableSet;

/**
 * Class PDOUserTable
 * A PDOTable for User Account Tables
 * Provides additional user-specific functionality and API endpoints
 * @package CPath\Framework\PDO
 */
abstract class PDOUserTable extends PDOPrimaryKeyTable {

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
    //const CACHE_ENABLED = true;

    /** Confirm Password by default */
    const PASSWORD_CONFIRM = true;

    ///** Specify the IUserSession class or model */
    //const SESSION_CLASS = NULL;

    private static $mSessionUser = array();

    /**
     * @return ISessionManager
     */
    abstract function session();

    /**
     * Returns the default IHandlerSet collection for this PDOModel type.
     * Note: if this method is called in a PDOModel thta does not implement IRoutable, a fatal error will occur
     * @param bool $readOnly
     * @param bool $allowDelete
     * @return RoutableSet a set of common routes for this PDOModel type
     */
    function loadDefaultRouteSet($readOnly=true, $allowDelete=false) {
        $Routes = RoutableSet::fromHandler($this);
        $Routes['GET'] = new GetAPI($this);
        $Routes['GET search'] = new GetSearchAPI($this);
        $Routes['GET browse'] = new GetBrowseAPI($this);

        if(!$readOnly)
            $Routes['POST'] = new PostUserAPI($this);
        $Routes['POST login'] = new PostUserLoginAPI($this);
        $Routes['POST logout'] = new PostUserLogoutAPI($this);
        $Routes['POST password'] = new PostUserPasswordAPI($this);
        $Routes->setDefault($Routes['GET browse']);
        return $Routes;
    }

//    /**
//     * Load all available actions from this object.
//     */
//    function loadDefaultTasks(ITaskCollection $Manager) {
//        \CPath\Framework\PDO\Table\Types\parent::loadDefaultTasks($Manager);
//        $Manager->add(new Task_Login($this));
//    }

    /**
     * Internal method inserts an associative array into the database.
     * Overwritten methods must include parent::insertRow($row);
     * @param array $row
     */
    protected function insertRow(Array $row) {
        if(isset($row[static::COLUMN_PASSWORD]))
            $row[static::COLUMN_PASSWORD] = static::hashPassword($row[static::COLUMN_PASSWORD]);
        if(!isset($row[static::COLUMN_FLAGS]))
            $row[static::COLUMN_FLAGS] = 0;
        \CPath\Framework\PDO\Table\Types\parent::insertRow($row);
    }

    /**
     * Confirm two passwords match
     * @param $newPassword
     * @param $confirmPassword
     * @throws PasswordMatchException if passwords do not match
     */
    function confirmPassword($newPassword, $confirmPassword) {
        if(strcmp($newPassword, $confirmPassword) !== 0)
            throw new PasswordMatchException();
    }

    /**
     * Hash passwords
     * @param $password
     * @return string
     */
    function hashPassword($password) {
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
     * Loads a user instance from a session key
     * Note: This method does not depend on the current session state
     * @param $key String the session key to search for
     * @return PDOUserModel the found user instance or primary key id of the user
     */
    function loadBySessionKey($key) {
        $S = $this->session();
        $Session = $S->loadByKey($key);
        return $this->loadByPrimaryKey($Session->getUserID());
    }



    /**
     * Get the current user session or return a guest account
     * @param bool $throwOnFail throws an exception if the user session was not available
     * @param bool $allowGuest returns a guest account if no session is available
     * @return PDOUserModel|IUser|NULL the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    function loadBySession($throwOnFail = true, $allowGuest = false) {
        $class = get_called_class();
        if(isset(self::$mSessionUser[$class]))
            return self::$mSessionUser[$class];

        if($throwOnFail && !$allowGuest)
            return self::$mSessionUser[$class] = static::loadByPrimaryKey($this->session()->loadBySession()->getUserID());
        try {
            return self::$mSessionUser[$class] = static::loadByPrimaryKey($this->session()->loadBySession()->getUserID());
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
    function loadGuestAccount(Array $insertFields=array()) {
        /** @var PDOUserModel $User  */
        $User = static::searchByColumns('guest', static::COLUMN_USERNAME)->fetch();
        if(!$User) {
            if(!isset($insertFields[static::COLUMN_FLAGS]))
                $insertFields[static::COLUMN_FLAGS] = 0;
            $insertFields[static::COLUMN_FLAGS] |= IUser::FLAG_GUEST;
            $User = static::createAndLoad($insertFields + array(
                static::COLUMN_USERNAME => 'guest',
                static::COLUMN_EMAIL => 'guest@noemail.com',
            ));
        }
        $User->addFlags(IUser::FLAG_GUEST);
        return $User;
    }

    /**
     * Log in to a user account
     * @param String $search the user account to search for
     * @param String $password the password to log in with
     * @param int $expireInSeconds the amount of time in seconds before an account should expire or 0 for never
     * @param PDOUserModel $User the user instance loaded during login
     * @throws AuthenticationException
     * @return \CPath\Response\IResponse the login response
     */
    public function login($search, $password, $expireInSeconds=NULL, PDOUserModel &$User=NULL) {
        /** @var PDOUserModel $User */
        $User = static::searchByColumns($search, array(
            static::COLUMN_USERNAME,
            static::COLUMN_EMAIL,
        ))->fetch();

        if(!$User)
            throw new AuthenticationException();

        $User->checkPassword($password);
        $Session = $this->session()->createNewSession($User, $expireInSeconds);

        $Response = new DataResponse("Logged in as user '".$User->getName()."' successfully", true, array(
            'user' => $User,
            'session' => $Session,
        ));
        return $Response;
    }

    /**
     * Log out of a user account
     * @return boolean if the log out was successful
     */
    function logout() {
        return $this
            ->session()
            ->destroySession();
    }
}