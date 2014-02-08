<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task\Parameter;

use CPath\Framework\Api\Interfaces\ValidationException;

class SimpleParameter implements ITaskParameter {

    private $mKey, $mValue;

    function __construct($key, $defaultValue=null) {
        $this->mKey = $key;
        $this->mValue = $defaultValue;
    }

    /**
     * Get the parameter key
     * @return String
     */
    function getKey() { return $this->mKey; }

    /**
     * Get the parameter value
     * @return String
     */
    function getValue() { return $this->mValue; }

    /**
     * Set the parameter value
     * @param String $value
     * @return mixed
     */
    function setValue($value) { $this->mValue = $value; }

    /**
     * Validate the parameter value
     * @return void
     * @throws ValidationException
     */
    function validate() {}
}
