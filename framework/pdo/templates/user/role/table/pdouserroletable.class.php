<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Role\Table;


use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\User\Role\Interfaces\IRole;
use CPath\Framework\User\Role\Interfaces\IRoleCollection;

abstract class PDOUserRoleTable extends PDOPrimaryKeyTable implements IRoleCollection {

    const COLUMN_USER_ID = NULL;
    const COLUMN_CLASS = NULL;
    const COLUMN_DATA = NULL;


    /**
     * Add a user role to the user account
     * @param $userID
     * @param IRole $Role
     * @internal param \CPath\Framework\User\Interfaces\IUser $User
     * @return void
     */
    function insertUserRole($userID, IRole $Role) {
        $this->insert(static::COLUMN_USER_ID, static::COLUMN_CLASS, static::COLUMN_DATA)
            ->values($userID, get_class($Role), $Role->serialize());
    }
}
