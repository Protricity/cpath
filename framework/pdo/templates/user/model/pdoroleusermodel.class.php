<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Model;

use CPath\Framework\PDO\Templates\User\Role\Model\PDOUserRoleModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;
use CPath\Framework\PDO\Templates\User\Table\PDORoleUserTable;
use CPath\Framework\User\Interfaces\IRoleUser;
use CPath\Framework\User\Role\Common\RoleList;
use CPath\Framework\User\Role\Interfaces\IRole;
use CPath\Framework\User\Role\Interfaces\IRoleCollection;


/**
 * Class PDORoleUserModel
 * A PDOModel for User Account Tables
 * Provides additional user-specific functionality and API endpoints
 * @package CPath\Framework\PDO
 */
// TODO: refactor into interfaces
abstract class PDORoleUserModel extends PDOUserModel implements IRoleUser {

    private $mRoles = null;

    /**
     * @return \CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable
     */
    function roleTable() {
        /** @var PDORoleUserTable $Table */
        $Table = $this->table();
        return $Table->roleTable();
    }

    /**
     * Load all user roles for this user
     * @param bool $force if true, skip cache
     * @return IRoleCollection
     */
    function loadUserRoles($force=false) {
        if($this->mRoles != null)
            return $this->mRoles;

        $RoleTable = $this->roleTable();

        $Search = $RoleTable
            ->search()
            ->where($RoleTable::COLUMN_USER_ID, $this->getID());

        $this->mRoles = new RoleList();

        /** @var PDOUserRoleModel $RoleModel */
        foreach($Search as $RoleModel) {
            $this->mRoles[] = $RoleModel->loadRoleInstance();
        }

        return $this->mRoles;
    }

    /**
     * Add a user role to the user account
     * @param IRole $Role
     * @return void
     */
    function addUserRole(IRole $Role) {
        $RoleTable = $this->roleTable();

        $RoleTable->insertUserRole($this->getID(), $Role);
    }
}