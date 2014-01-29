<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User;

use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\User\Session\ISession;
use CPath\Framework\User\Session\ISessionManager;

abstract class PDOUserSessionModel extends PDOPrimaryKeyModel implements ISession {

    /**
     * @return \CPath\Framework\User\Session\ISessionManager
     */
    abstract function session();

    /**
     * @return PDOUserSessionTable
     */
    abstract function table();

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID() {
        $T = $this->table();
        return $this->{$T::COLUMN_USER_ID};
    }


}
