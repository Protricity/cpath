<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IUser {
    const FlagDisabled = 0x01;
    const FlagValidated = 0x02;
    const FlagGuest = 0x04;

    const FlagDebug = 0x10;
    const FlagManager = 0x20;
    const FlagAdmin = 0x40;

    function getID();

    /**
     * Loads a user instance from the active session
     * @return IUser|NULL the found user instance or null if not found
     */
    static function loadBySession();
}