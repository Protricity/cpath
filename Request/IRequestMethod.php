<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 10:19 PM
 */
namespace CPath\Request;

use CPath\Request\Executable\IPrompt;

interface IRequestMethod extends IPrompt
{
    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName();
}