<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Framework\Api\Interfaces\ValidationException;
use CPath\Interfaces\IValidate;

/**
 * Class Util provides information about the current request
 * @package CPath
 */
class Validate implements IValidate {
    const FILTER_VALIDATE_STRING = 2001;

    const FILTER_VALIDATE_USERNAME = 2021;

    const DEFAULT_USERNAME_MIN_LENGTH = 3;
    const DEFAULT_USERNAME_MAX_LENGTH = 28;
    const DEFAULT_USERNAME_PREG_MATCH = '/^[\w_-]+$/';
    const DEFAULT_USERNAME_MESSAGE = 'Username may only contain numbers, letters, underscore [_] or dash [-]';
    // Change via validation.username = array([min], [max], [regex], [message]);

    const FILTER_VALIDATE_PASSWORD = 2022;
    const DEFAULT_PASSWORD_MIN_LENGTH = 5;
    const DEFAULT_PASSWORD_MAX_LENGTH = 28;
    const DEFAULT_PASSWORD_PREG_MATCH = false;
    const DEFAULT_PASSWORD_MESSAGE = 'Invalid Password';

    protected function __construct() {}

    /**
     * Validate input based on a filter type.
     * Throws a validation exception or returns a filtered variable
     * @param mixed $variable the variable to filter/validate
     * @param int $filter the filter id to use
     * @param null|mixed $options additional filter options
     * @return mixed the value of the filtered variable
     * @throws ValidationException when the input fails to validate
     */
    function validateInput($variable, $filter, $options = NULL) {
        switch($filter) {
            case self::FILTER_VALIDATE_STRING:
                if(strlen($variable) < $options['min'])
                    throw new ValidationException("Field '%s' must be at least {$options['min']} characters long");
                if(strlen($variable) > $options['max'])
                    throw new ValidationException("Field '%s' must be no more than {$options['max']} characters long");
                if($options['reg']) {
                    if(!preg_match($options['reg'], $variable))
                        throw new ValidationException($options['reg_message'] ?: "Field '%s' does not match ".$options['reg']);
                } else {
                    $variable = filter_var($variable, FILTER_SANITIZE_SPECIAL_CHARS);
                }
                return $variable;

            case self::FILTER_VALIDATE_USERNAME:
                $options = (array)$options
                    + Config::$ValidationUsername
                    + array(
                        'min' => self::DEFAULT_USERNAME_MIN_LENGTH,
                        'max' => self::DEFAULT_USERNAME_MAX_LENGTH,
                        'reg' => self::DEFAULT_USERNAME_PREG_MATCH,
                        'reg_message' => self::DEFAULT_USERNAME_MESSAGE);
                return $this->validateInput($variable, self::FILTER_VALIDATE_STRING, $options);

            case self::FILTER_VALIDATE_PASSWORD:
                $options = (array)$options
                    + Config::$ValidationPassword
                    + array(
                        'min' => self::DEFAULT_PASSWORD_MIN_LENGTH,
                        'max' => self::DEFAULT_PASSWORD_MAX_LENGTH,
                        'reg' => self::DEFAULT_PASSWORD_PREG_MATCH,
                        'reg_message' => self::DEFAULT_PASSWORD_MESSAGE);
                return $this->validateInput($variable, self::FILTER_VALIDATE_STRING, $options);

            case FILTER_VALIDATE_BOOLEAN:
                $variable = filter_var($variable);
                if($variable === NULL)
                    throw new ValidationException("Invalid boolean input");
        }
        $variable = filter_var($variable, $filter, $options);
        if($variable === false)
            throw new ValidationException("Field '%s' is not in the correct format ({$filter})");
        return $variable;
    }

    // Statics

    /**
     * Validate input based on a filter type.
     * Throws a validation exception or returns a filtered variable
     * @param mixed $variable the variable to filter/validate
     * @param int $filter the filter id to use
     * @param null|mixed $options additional filter options
     * @return mixed the value of the filtered variable
     * @throws ValidationException when the input fails to validate
     */
    static function input($variable, $filter, $options = NULL) {
        return self::get()->validateInput($variable, $filter, $options);
    }

    static function inputField($fieldName, $variable, $filter, $options = NULL) {
        try {
            return self::get()->validateInput($variable, $filter, $options);
        } catch (ValidationException $ex) {
            $ex->updateMessage($fieldName);
            throw $ex;
        }
    }

    static function get() {
        static $inst = NULL;
        return $inst ?: new Validate();
    }
}
