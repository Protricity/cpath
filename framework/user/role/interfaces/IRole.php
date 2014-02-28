<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role\Interfaces;

use CPath\Framework\Data\Collection\ICollectionItem;
use CPath\Framework\Data\Serialize\Interfaces\ISerializable;
use CPath\Framework\User\Role\Exceptions\InvalidRoleException;

interface IRole extends ICollectionItem, ISerializable {

    /**
     * Assert this user role or throw an exception
     * @return void
     * @throws InvalidRoleException if the user role assertion fails
     */
    function assert();
}
