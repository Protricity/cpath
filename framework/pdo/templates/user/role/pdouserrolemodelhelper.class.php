<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Model;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Framework\PDO\Templates\User\Role\Model\PDOUserRoleModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;
use CPath\Framework\User\Role\Interfaces\IRole;

class PDOUserRoleModelHelper implements IPDOModelHelper {

    const PHP_GETUSERID <<<'PHP'
    function getUserID() { $T = $this->table(); return $this->{$T::COLUMN_USER_ID}; }
PHP;

    const PHP_GETDATA <<<'PHP'
    function getData() { $T = $this->table(); return $this->{$T::COLUMN_USER_ID}; }
PHP;

    const PHP_GETUSERID <<<'PHP'
    function getUserID() { $T = $this->table(); return $this->{$T::COLUMN_USER_ID}; }
PHP;

    const PHP_LOADROLEINSTANCE <<<'PHP'
    function loadRoleInstance() { $T = $this->table(); return $this->{$T::COLUMN_USER_ID}; }
PHP;


    /** @var IRole */
    private $mRole;


    function processPHP(BuildPHPClass $PHP) {
        $PHP->addMethod('getUserID', '', self::PHP_GETUSERID);
        $PHP->addMethod('getData', '', self::PHP_GETUSERID);
        $PHP->addMethod('getRoleClass', '', self::PHP_GETUSERID);
    }


    /**
     * @param PDOUserRoleModel $Role
     * @return IRole
     */
    function loadRoleInstance(PDOUserRoleModel $Role) {
        /** @var \CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable $T */
        $T = $Role->table();

        /** @var IRole $rollClass */
        $rollClass = $this->{$T::COLUMN_CLASS};
        $data = $this->{$T::COLUMN_DATA};

        return $rollClass::unserialize($data);
    }
}
