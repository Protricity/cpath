<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Session;

use CPath\Response\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseAggregate;

interface ISession {

    /**
     * Get the User PRIMARY Key ID associated with this Session
     * @return String User ID
     */
    function getUserID();

    /**
     * Ent a user account
     * @return boolean true if the session was destroyed
     */
    function end();


}