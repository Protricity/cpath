<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Model;

use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;


use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\User\Interfaces\IUser;
use CPath\Framework\User\Role\Exceptions\AuthenticationException;
use CPath\Framework\User\Session\ISessionManager;


/**
 * Class PDOUserModel
 * A PDOModel for User Account Tables
 * Provides additional user-specific functionality and API endpoints
 * @package CPath\Framework\PDO
 */
abstract class PDOUserModel extends PDOPrimaryKeyModel implements IUser {

    /** Confirm Password by default */
    //const PASSWORD_CONFIRM = true;

//    public function __construct() {
//        $this->mFlags = (int)$this->{$T::COLUMN_FLAGS};
//    }

//    public function getUsername() { return $this->{$T::COLUMN_USERNAME}; }
//    public function setUsername($value, $commit=true) { return $this->updateColumn($T::COLUMN_USERNAME, $value, $commit); }
//
//    public function getEmail() { return $this->{$T::COLUMN_EMAIL}; }
//    public function setEmail($value, $commit=true) { return $this->updateColumn($T::COLUMN_EMAIL, $value, $commit, FILTER_VALIDATE_EMAIL); }


    /**
     * @return PDOUserTable
     */
    abstract function table();

    /**
     * @return ISessionManager
     */
    function session() {
        return $this
            ->table()
            ->session();
    }


    function getFlags() {
        $T = $this->table();
        return (int)$this->{$T::COLUMN_FLAGS};
    }

    function addFlags($flags, $commit=true) {
        $T = $this->table();
        if(!is_int($flags))
            throw new \InvalidArgumentException("addFlags 'flags' parameter must be an integer");
        $flags = $this->getFlags() & ~$flags;
        $this->updateColumn($T::COLUMN_FLAGS, $flags, $commit);
    }

    function removeFlags($flags, $commit=true) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("removeFlags 'flags' parameter must be an integer");

        $flags = $this->getFlags() & ~$flags;
        $T = $this->table();
        $this->updateColumn($T::COLUMN_FLAGS, $flags, $commit);
    }

    function checkPassword($password) {
        $T = $this->table();
        $hash = $this->{$T::COLUMN_PASSWORD};
        if($T->hashPassword($password, $hash) == $hash)
            throw new AuthenticationException();
    }

    function changePassword($newPassword, $confirmPassword=NULL) {
        $T = $this->table();
        if($confirmPassword !== NULL)
            $T->confirmPassword($newPassword, $confirmPassword);
        if(!$newPassword)
            throw new \InvalidArgumentException("Empty password provided");
        $this->updateColumn($T::COLUMN_PASSWORD, $newPassword, true); // It should auto hash
    }

    /**
     * Returns true if the user is a guest account
     * @return boolean true if user is a guest account
     */
    function isGuestAccount() {
        return $this->getFlags() & IUser::FLAG_GUEST ? true : false;
    }

//    /**
//     * Returns true if the user is an admin
//     * @return boolean true if user is an admin
//     */
//    function isAdmin() {
//        return $this
//            ->loadUserRoles()
//            ->has(new IsAdmin());
//    }
//
//    /**
//     * Returns true if the user is viewing debug mode
//     * @return boolean true if user is viewing debug mode
//     */
//    function isDebug() {
//        return $this
//            ->loadUserRoles()
//            ->has(new IsDebugger());
//    }


    /**
     * UPDATE a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true) {
        $T = $this->table();
        if($column == $T::COLUMN_PASSWORD)
            $value = $T->hashPassword($value);
        return parent::updateColumn($column, $value, $commit);
    }
}