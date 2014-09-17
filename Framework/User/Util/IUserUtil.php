<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Util;

use CPath\Framework\User\Interfaces\IUser;
use CPath\Framework\User\Role\Exceptions\InvalidRoleException;
use CPath\Framework\User\Role\Interfaces\IRoleProfile;

interface IUserUtil extends IUser {

    /**
     * Assert a user role and return the result as a boolean
     * @param \CPath\Framework\User\Role\Interfaces\IRoleProfile $Profile the role .profile to assert
     * @return bool true if the role exists and the assertion passed
     */
    function hasRole(IRoleProfile $Profile);

    /**
     * Assert a user role or throw an InvalidRoleException
     * @param IRoleProfile $Profile the role .profile to assert
     * @throws InvalidRoleException if the user role assertion fails
     */
    function assertRole(IRoleProfile $Profile);
}