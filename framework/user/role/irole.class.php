<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role;

use CPath\Serializer\ISerializable;
use CPath\Type\Collection\ICollectionItem;

interface IRole extends ICollectionItem, ISerializable {

    /**
     * Assert this user role or throw an exception
     * @param IRole $Role the role configuration to assert against
     * @return void
     * @throws InvalidRoleException if the user role assertion fails
     */
    function assert(IRole $Role);
}

class RoleException extends \Exception {}