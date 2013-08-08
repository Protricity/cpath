<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/7/13
 * Time: 10:39 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Exceptions;

/**
 * Class ValidationException
 * @package CPath
 * Thrown when input fails to validate
 */
class ValidationException extends \Exception {
    public function getFieldError($fieldName) {
        return strpos($msg = $this->getMessage(), '%s') !== false
            ? sprintf($msg, $fieldName)
            : $msg;
    }

    /**
     * @param $fieldName
     * @return ValidationException
     */
    public function updateMessage($fieldName) {
        $this->message = $this->getFieldError($fieldName);
        return $this;
    }
}