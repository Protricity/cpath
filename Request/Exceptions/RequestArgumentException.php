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
    private $mArgName;

    public function __construct($msg, $argName)
    {
        parent::__construct($msg);
        $this->mArgName = $argName;
    }
}