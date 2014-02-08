<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task\Parameter;

use CPath\Framework\Api\Interfaces\ValidationException;

interface ITaskParameter {

    /**
     * Get the parameter key
     * @return String
     */
    function getKey();

    /**
     * Get the parameter value
     * @return String
     */
    function getValue();

    /**
     * Set the parameter value
     * @param String $value
     * @return mixed
     */
    function setValue($value);

    /**
     * Validate the parameter value
     * @return void
     * @throws ValidationException
     */
    function validate();
}
