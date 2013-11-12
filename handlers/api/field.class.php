<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\IField;
use CPath\Interfaces\IDescribable;
use CPath\Validate;


/**
 * Class APIField
 * @package CPath
 * Represents an 'optional' API Field
 */
class Field implements IField {

    private $mName, $mDescription, $mValidation;
    private $mShortNames=array();

    /**
     * @param String|IDescribable $Description
     * @param int $validation
     */
    public function __construct($Description=NULL, $validation=0) {
        $this->mDescription = $Description;
        $this->mValidation = $validation;
    }

    public function setValidation($filter) {
        $this->mValidation = $filter;
        return $this;
    }

    public function validate($value) {
        if($value === "")
            $value = NULL;
        if($this->mValidation)
            Validate::input($value, $this->mValidation);
        return $value;
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return $this->mDescription;
    }


    /**
     * Internal function used to set the field name.
     * @param String $name
     * @return void
     * @throws \Exception if the name was already set.
     */
    function setName($name) {
        if($this->mName !== null)
            throw new \Exception("Name '" . $name ."' was set twice");
        $this->mName = $name;
    }

    /**
     * Get the field name.
     * @return string
     * @throws \Exception if the name was never set.
     */
    function getName() {
        if($this->mName === null)
            throw new \Exception("Name was not set yet");
        return $this->mName;
    }

    /**
     * Adds a short alias to the field.
     * @param String $shortName
     * @return void
     */
    function addShortName($shortName) {
        $this->mShortNames[] = $shortName;
    }

    /**
     * Returns a list of short names for this field.
     * @return Array returns an array of short names
     */
    function getShortNames() {
        return $this->mShortNames;
    }
}