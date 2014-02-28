<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\CLI\Option\Type;

use CPath\Framework\CLI\Option\Interfaces\IOptionMap;
use CPath\Framework\CLI\Option\Interfaces\OptionMissingException;

class OptionMap implements IOptionMap {

    private $mMap = array();
    private $mCounter = 97;

    /**
     * Match an option against a map and return the value if found
     * @param $option
     * @return String
     * @throws OptionMissingException if the option was not found
     */
    function matchOption($option) {
        foreach($this->mMap as $key => $value) {
            if($option === $key)
                return $value;
        }
        throw new OptionMissingException("Option '{$option}' not found");
    }

    function addShortByField($name) {

        $short = '';
        foreach(explode('_', $name) as $f2)
            $short .= $f2[0];

        $short = strtolower($short);
        if(!isset($this->mMap[$short])) {
            $this->mMap[$short] = $name;
        } else {
            while(isset($this->mMap[chr($this->mCounter)]))
                $this->mCounter++;
            if($this->mCounter > 122)
                return; // TODO: finish?

            $this->mMap[chr($this->mCounter)] = $name;
        }
    }
}