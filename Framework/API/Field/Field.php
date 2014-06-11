<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Field;

use CPath\Describable\IDescribable;
use CPath\Describable\IDescribableAggregate;
use CPath\Framework\API\Exceptions\RequiredFieldException;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Interfaces;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\Util\HTMLRenderUtil;
use CPath\Validate;


/**
 * Class Field
 * @package CPath
 * Represents an 'optional' API Field
 */
class Field implements IField, IDescribableAggregate, IRenderHTML {

    private $mName, $mDescription, $mValidation, $mDefaultValue = null, $mFlags = 0, $mValue=null;

    /**
     * Create a new API Field
     * @param $name
     * @param String|IDescribable $Description
     * @param int $validation
     * @param int $flags
     */
    public function __construct($name, $Description=NULL, $validation=0, $flags=0) {
        $this->mName = $name;
        $this->mDescription = $Description;
        $this->mValidation = $validation;
        $this->mFlags = $flags;
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
     * @throws \CPath\Framework\API\Exceptions\ValidationException if validation fails
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
        if($this->mFlags & IField::IS_REQUIRED)
            $this->validateRequired($value);
        return $value;
    }

    /**
     * Test required field value
     * @param $value
     * @throws \CPath\Framework\API\Exceptions\RequiredFieldException
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
     * @return \CPath\Framework\API\Field\Interfaces\IField
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
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);

        $value = $this->mValue ?: $Request[$this->getName()];
        if($value)
            $value = htmlspecialchars($value, ENT_QUOTES);

        if(!$Attr->has('name'))
            $Attr->add('name', $this->getName());

        if(!$Attr->has('value'))
            $Attr->add('value', $value);

        if(!$Attr->has('placeholder'))
            $Attr->add('placeholder', $this->getName() . ' value');

        $Util = new HTMLRenderUtil($Request);
        $Util->render('input', $Attr);
//        $Attr->render($Request);
//        echo RI::ni(), "<input";
//        foreach($attr as $key=>$val)
//            echo " $key='$val'";
//        echo "/>";
    }

    /**
     * Returns the field flags
     * @return int
     */
    function getFieldFlags() { return $this->mFlags; }

}