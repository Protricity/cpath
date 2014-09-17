<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role\Common;

use CPath\Framework\Data\Collection\AbstractCollection;
use CPath\Framework\User\Role\Exceptions\InvalidRoleException;
use CPath\Framework\User\Role\Interfaces\IRole;
use CPath\Framework\User\Role\Interfaces\IRoleCollection;
use CPath\Framework\User\Role\Interfaces\IRoleProfile;

class RoleList extends AbstractCollection implements IRoleCollection {

    /**
     * Add an IRole to the collection
     * @param IRole $Role
     * @return IRoleCollection return self
     */
    function addRole(IRole $Role) {
        $this->addItem($Role);
    }

    /**
     * Assert a user role and return the result as a boolean
     * @param IRoleProfile $Profile the IRoleProfile to assert
     * @return bool true if the role exists and the assertion passed
     */
    function has(IRoleProfile $Profile) {
        return $this->where($Profile)->count() >= 1;
    }

    /**
     * Calls ->assert on all roles in the collection
     * @param IRoleProfile|null $Profile optional .profile to assert
     * @throws InvalidRoleException if the user role assertion fails
     * @return void
     */
    function assert(IRoleProfile $Profile = null) {
        $List = $this->where($Profile);
        $Profile->assert($List);
    }

}
