<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/7/14
 * Time: 8:08 PM
 */
namespace CPath\Framework\API\Exceptions;
/**
 * Class ValidationException
 * @package CPath
 * Thrown when input fails to validate
 */
class ValidationException extends \Exception
{
    public function getFieldError($fieldName)
    {
        return strpos($msg = $this->getMessage(), '%s') !== false
            ? sprintf($msg, $fieldName)
            : $msg;
    }

    /**
     * @param $fieldName
     * @return ValidationException
     */
    public function updateMessage($fieldName)
    {
        $this->message = $this->getFieldError($fieldName);
        return $this;
    }
}