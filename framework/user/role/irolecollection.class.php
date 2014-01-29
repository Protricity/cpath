<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role;

use CPath\Type\Collection\ICollection;

interface IRoleCollection extends ICollection {

    /**
     * Add an IRole to the collection
     * @param IRole $Task
     * @return IRoleCollection return self
     */
    function add(IRole $Task);

    /**
     * Assert a user role and return the result as a boolean
     * @param IRole $Role the IRole configuration to compare against
     * @return bool true if the role exists and the assertion passed
     * @throws InvalidRoleException if the user role assertion fails
     */
    function has(IRole $Role);

    /**
     * Assert a user role. Performs IRole::assert($Role) on all rolls found matching the class name as filtered
     * @param IRole $Role the IRole instance to compare classes against
     * @throws RoleNotFoundException if the role was not found in the collection as filtered
     * @throws InvalidRoleException if the user role assertion fails
     * @return void
     */
    function assert(IRole $Role);
}

class RoleNotFoundException extends RoleException {}
class InvalidRoleException extends RoleException {}
