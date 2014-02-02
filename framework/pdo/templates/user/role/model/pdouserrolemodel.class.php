<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Role\Model;

use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;
use CPath\Framework\User\Role\Interfaces\IRole;
use CPath\Framework\User\Session\ISession;

abstract class PDOUserRoleModel extends PDOPrimaryKeyModel implements ISession {

    /** @var IRole */
    private $mRole;

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID() {
        /** @var \CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable $T */
        $T = $this->table();
        return $this->{$T::COLUMN_USER_ID};
    }

    /**
     * @return IRole
     */
    function loadRoleInstance() {

        if($this->mRole)
            return $this->mRole;

        /** @var \CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable $T */
        $T = $this->table();

        /** @var IRole $rollClass */
        $rollClass = $this->{$T::COLUMN_CLASS};
        $data = $this->{$T::COLUMN_DATA};

        return $this->mRole = $rollClass::unserialize($data);
    }
}
