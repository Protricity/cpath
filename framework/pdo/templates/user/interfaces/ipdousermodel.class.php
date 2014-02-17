<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Model;

use CPath\Framework\PDO\Table\Model\Interfaces\IPDOPrimaryKeyModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\User\Interfaces\IUser;
use CPath\Framework\User\Session\ISessionManager;


/**
 * Class PDOUserModel
 * A PDOModel for User Account Tables
 * Provides additional user-specific functionality and API endpoints
 * @package CPath\Framework\PDO
 */
interface IPDOUserModel extends IPDOPrimaryKeyModel, IUser {

    /**
     * @return PDOUserTable
     */
    function table();

    /**
     * @return ISessionManager
     */
    function session();


    function getFlags();

    function addFlags($flags, $commit=true);

    function removeFlags($flags, $commit=true);

    function checkPassword($password);

    function changePassword($newPassword, $confirmPassword=NULL);

    /**
     * UPDATE a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true);
}