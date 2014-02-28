<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/7/14
 * Time: 8:09 PM
 */
namespace CPath\Framework\Api\Exceptions;
use CPath\Describable\Describable;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Types\Exceptions\MultiException;

/**
 * Class ValidationExceptions
 * @package CPath
 * Throw when one or more Fields fails to validate
 */
class ValidationExceptions extends MultiException
{
    public function __construct(IAPI $API, $message = NULL)
    {
        parent::__construct("Errors occurred in API '" . Describable::get($API)->getDescription() . "':\n");
    }

    public function addFieldException($fieldName, ValidationException $ex)
    {
        parent::add($ex->getFieldError($fieldName), $fieldName);
    }
}