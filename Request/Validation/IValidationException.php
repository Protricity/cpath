<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/28/14
 * Time: 10:36 AM
 */
namespace CPath\Request\Validation;

interface IRequestValidationException
{
    /**
     * Get an associative array of parameter values or exceptions by name
     * @return Array
     */
    function getRequestValues();
}

