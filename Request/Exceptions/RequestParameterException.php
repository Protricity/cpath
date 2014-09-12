<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 11:44 PM
 */
namespace CPath\Request\Exceptions;

class RequestParameterException extends \Exception
{
    public function __construct($msg, $paramName, $description=null) {
        parent::__construct($msg);
    }
}

