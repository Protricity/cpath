<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

use CPath\Model\DB\ModelNotFoundException;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;

interface IUserSession {
    function getID();

    function getName();

    function getFlags();
    function setFlags($value, $commit=true);

    function getPassword();
    function setPassword($value, $commit=true);

    /**
     * Returns true if the user is an admin
     * @return boolean true if user is an admin
     */
    function isAdmin();

    /**
     * Returns true if the user is viewing debug mode
     * @return boolean true if user is viewing debug mode
     */
    function isDebug();

    // Statics

    /**
     * Stores a session key for a corresponding user id
     * @param $key String the session key to store
     * @param $user_id int the user id to store with the session key
     * @return void
     */
    static function storeNewSessionKey($key, $user_id);

    /**
     * Loads a user instance from a session key
     * @param $key String the session key to search for
     * @return PDOModel|mixed the found user instance or primary key id of the user
     */
    static function loadFromSessionKey($key);

    /**
     * Deletes or disables a session key from the database
     * @param $key String the session key to search for
     * @return void
     */
    static function disableSessionKey($key);

    /**
     * Gets or creates an instance of a guest user
     * @return IUserSession|PDOModel the guest user instance
     */
    static function loadGuestAccount();

    /**
     * Log in to a user account
     * @param $search String the user account to search for
     * @param $password String the password to log in with
     * @return IUserSession|PDOModel The logged in user instance
     */
    static function login($search, $password);

    /**
     * Log out of a user account
     * @return IUserSession|PDOModel The guest user account
     */
    static function logout();

    /**
     * Get the current user session or return a guest account
     * @return IUserSession|PDOModel the user instance
     */
    static function getUserSession();

    /**
     * Loads a model based on a primary key column value
     * @param $search String the value to search for
     * @return IUserSession|PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     * @throws \Exception if the model does not contain primary keys
     */
    static function loadByPrimaryKey($search);

    /**
     * Searches a Model based on specified fields and values.
     * @param array $fields an array of key-value pairs to search for
     * @param int $limit the number of rows to return
     * @param string $logic 'OR' or 'AND' logic between fields
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    static function searchByFields(Array $fields, $limit=1, $logic='OR');

    /**
     * Searches for a Model using all indexed fields.
     * @param mixed $any a value to search for
     * @param int $limit the number of rows to return
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if the model does not contain index keys
     */
    static function searchByAnyIndex($any, $limit=1);

}
