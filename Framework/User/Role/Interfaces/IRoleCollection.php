<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role\Interfaces;


use CPath\Framework\Data\Collection\ICollection;
use CPath\Framework\User\Role\Exceptions\InvalidRoleException;

interface IRoleCollection extends ICollection {

    /**
     * Add an IRole to the collection
     * @param IRole $Task
     * @return IRoleCollection return self
     */
    function addRole(IRole $Task);

    /**
     * Assert a user role and return the result as a boolean
     * @param IRoleProfile $Profile the IRoleProfile to assert
     * @return bool true if the role exists and the assertion passed
     */
    function has(IRoleProfile $Profile);

    /**
     * Calls ->assert on all roles in the collection
     * @param IRoleProfile|null $Profile optional profile to assert
     * @throws InvalidRoleException if the user role assertion fails
     * @return void
     */
    function assert(IRoleProfile $Profile=null);
}



