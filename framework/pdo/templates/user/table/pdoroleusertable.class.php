<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Table;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;

/**
 * Class PDORoleUserTable
 * A PDOTable for User Account Tables
 * Provides additional user-specific functionality and API endpoints
 * @package CPath\Framework\PDO
 */
abstract class PDORoleUserTable extends PDOUserTable {

    /**
     * @return PDOUserRoleTable
     */
    abstract function roleTable();
}