<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Interfaces;

use CPath\Framework\User\Role\Interfaces\IRole;
use CPath\Framework\User\Role\Interfaces\IRoleCollection;

interface IRoleUser extends IUser {

    /**
     * Load all user roles for this user
     * @param bool $force if true, skip cache
     * @return IRoleCollection
     */
    function loadUserRoles($force=false);

    /**
     * Add a user role to the user account
     * @param IRole $Role
     * @return void
     */
    function addUserRole(IRole $Role);
}

