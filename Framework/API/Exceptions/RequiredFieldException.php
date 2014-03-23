<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/7/14
 * Time: 8:08 PM
 */
namespace CPath\Framework\Api\Exceptions;
/**
 * Class RequiredFieldException
 * @package CPath
 * Throw when a required field is missing
 */
class RequiredFieldException extends ValidationException
{
    function __construct($msg = "Field '%s' is required")
    {
        parent::__construct($msg);
    }
}