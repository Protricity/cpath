<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IUser {
    const FLAG_DISABLED = 0x01;
    const FLAG_VALIDATED = 0x02;
    const FLAG_GUEST = 0x04;

    const FLAG_DEBUG = 0x10;
    const FLAG_MANAGER = 0x20;
    const FLAG_DEVELOPER = 0x40;
    const FLAG_ADMIN = 0x80;

    function getID();

    /**
     * Load or get the current user session
     * @return IUserSession the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    static function loadSession();

    /**
     * Load or get the current user via session or return a guest account
     * @param bool $throwOnFail throws an exception if the user session was not available
     * @return IUser|NULL the user instance or null if not found
     * @throws InvalidUserSessionException if the user is not logged in
     */
    static function loadBySession($throwOnFail = true);
}