<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User;

use CPath\Framework\User\Role\InvalidRoleException;
use CPath\Framework\User\Role\IRole;
use CPath\Framework\User\Role\IRoleCollection;
use CPath\Framework\User\Session\InvalidUserSessionException;
use CPath\Framework\User\Session\ISessionManager;

interface IUser {
    const FLAG_DISABLED = 0x01;
    const FLAG_VALIDATED = 0x02;
    const FLAG_GUEST = 0x04;

    const FLAG_DEBUG = 0x10;
    const FLAG_MANAGER = 0x20;
    const FLAG_DEVELOPER = 0x40;
    const FLAG_ADMIN = 0x80;

    /**
     * Get User ID
     * @return mixed
     */
    function getID();

    /**
     * Get Username
     * @return String
     */
    function getUsername();

    /**
     * Get User Email Address
     * @return String
     */
    function getEmail();

    /**
     * Load all user roles
     * @return IRoleCollection|IRole[]
     */
    function loadUserRoles();

    /**
     * Assert a user role and return the result as a boolean
     * @param IRole $Role the IRole configuration to compare against
     * @return bool true if the role exists and the assertion passed
     * @throws InvalidRoleException if the user role assertion fails
     */
    function hasRole(IRole $Role);

    /**
     * Assert a user role or thrown an exception
     * @param IRole $Role the IRole configuration to assert
     * @return void
     * @throws InvalidRoleException if the user role assertion fails
     */
    function assertRole(IRole $Role);

    /**
     * Load or get the current user session
     * @return ISessionManager the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    static function loadSession();

    /**
     * Load or get the current user via session or return a guest account
     * @param bool $throwOnFail throws an exception if the user session was not available
     * @return IUser|NULL the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    static function loadBySession($throwOnFail = true);
}



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