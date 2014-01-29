<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


use CPath\Handlers\Api\Interfaces\ValidationException;

interface IValidate {
    /**
     * Validate input based on a filter type.
     * Throws a validation exception or returns a filtered variable
     * @param mixed $variable the variable to filter/validate
     * @param int $filter the filter id to use
     * @param null|mixed $options additional filter options
     * @return mixed the value of the filtered variable
     * @throws ValidationException when the input fails to validate
     */
    function validateInput($variable, $filter, $options=NULL);
}