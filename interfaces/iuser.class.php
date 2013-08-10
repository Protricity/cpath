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
    const FLAG_ADMIN = 0x40;

    function getID();

    /**
     * Loads a user instance from the active session
     * @return IUser|NULL the found user instance or null if not found
     */
    static function loadBySession();
}