<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\CLI\Option\Interfaces;

class OptionMissingException extends \Exception {}

interface IOptionMap {

    /**
     * Match an option against a map and return the value if found
     * @param $option
     * @return String
     * @throws OptionMissingException if the option was not found
     */
    function matchOption($option);
}