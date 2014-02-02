<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\User\Role\Interfaces;


use CPath\Framework\User\Role\Exceptions\InvalidRoleException;
use CPath\Type\Collection\IPredicate;

interface IRoleProfile extends IPredicate {

    /**
     * Assert Role profile using found roles
     * @param IRoleCollection $FoundRoles
     * @throws InvalidRoleException if the user role assertion fails
     */
    function assert(IRoleCollection $FoundRoles);
}