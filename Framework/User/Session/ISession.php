<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Session;

interface ISession {

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID();

    /**
     * End a user session
     * @throws SessionNotFoundException if the session was not found
     */
    function endSession();


}