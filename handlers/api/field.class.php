<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\Interfaces\RequiredFieldException;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;
use CPath\Validate;


/**
 * Class Field
 * @package CPath
 * Represents an 'optional' API Field
 */
class Field implements IField {

    private $mName, $mDescription, $mValidation, $mDefaultValue = null, $mRequired = false, $mIsParam = false, $mValue=null;
    private $mShortNames=array();

    /**
     * Create a new API Field
     * @param String|\CPath\Describable\IDescribable $Description
     * @param int $validation
     * @param bool $isRequired
     * @param bool $isParam
     */
    public function __construct($Description=NULL, $validation=0, $isRequired=false, $isParam=false) {
        $this->mDescription = $Description;
        $this->mValidation = $validation;
        $this->mRequired = $isRequired;
        $this->mIsParam = $isParam;
    }

    public function setDefaultValue($value) {
        $this->mDefaultValue = $value;
        return $this;
    }

    public function setValidation($filter) {
        $this->mValidation = $filter;
        return $this;
    }

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     * @throws RequiredFieldException if a required field has no value
     */
    function validate(IRequest $Request, $fieldName) {
        $value = $Request[$fieldName];
        if($value === "")
            $value = NULL;
        if($value === NULL && $this->mDefaultValue)
            $value = $this->mDefaultValue;
        if($this->mValidation)
            Validate::input($value, $this->mValidation);
        if($this->mRequired)
            $this->validateRequired($value);
        return $value;
    }

    /**
     * Test required field value
     * @param $value
     * @throws Interfaces\RequiredFieldException
     */
    protected function validateRequired($value) {
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
    }

    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
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
     * Internal function used to set the field name.
     * @param String $value
     * @return IField
     */
    function setValue($value) {
        $this->mValue = $value;
        return $this;
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

    /**
     * Returns true if this Field is a Param Field
     * @return bool
     */
    function isParam() {
        return $this->mIsParam;
    }

    /**
     * Returns true if this Field is a required Field
     * @return bool
     */
    function isRequired() {
        return $this->mRequired;
    }

    /**
     * Render this input field as html
     * @param IRequest $Request the IRequest instance for this render
     * @param Array $attr optional array of attributes for the input field
     * @return void
     */
    function render(IRequest $Request, Array $attr=array())
    {
        $value = $this->mValue ?: $Request[$this->getName()];
        if($value)
            $value = htmlspecialchars($value, ENT_QUOTES);
        if(!isset($attr['name']))
            $attr['name'] = $this->getName();
        if(!isset($attr['value']))
            $attr['value'] = $value;
        if(!isset($attr['placeholder']))
            $attr['placeholder'] = $this->getName() . ' value';

        echo RI::ni(), "<input";
        foreach($attr as $key=>$val)
            echo " $key='$val'";
        echo "/>";
    }
}