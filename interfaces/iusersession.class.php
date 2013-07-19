<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IUserSession {
    function getID();

    function getFlags();
    function setFlags($value, $commit=true);

    function getPassword();
    function setPassword($value, $commit=true);

    function storeNewSessionKey($key, $user_id);

    // Statics

    static function login($search, $password);
    static function getUserSession();
}
