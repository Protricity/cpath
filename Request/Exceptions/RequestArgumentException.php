<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/15/14
 * Time: 9:19 PM
 */
namespace CPath\Request\Exceptions;

class RequestArgumentException extends \Exception
{
    private $mParamName;

    public function __construct($msg, $description = null)
    {
        parent::__construct($msg);
        $this->mParamName = $paramName;
    }
}