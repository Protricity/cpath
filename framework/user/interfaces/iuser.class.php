<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Interfaces;

interface IUser {
    const FLAG_DISABLED = 0x01;
    const FLAG_VALIDATED = 0x02;
    const FLAG_GUEST = 0x04;

    /**
     * Get User ID
     * @return mixed
     */
    function getID();

    /**
     * Get Username
     * @return String
     */
    function getUsername();

    /**
     * Get User Email Address
     * @return String
     */
    function getEmail();
}

