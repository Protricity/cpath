<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Interfaces;

use CPath\Framework\Request\Interfaces\IRequest;

class FieldUtil implements IFieldUtil {
    private $mField;

    function __construct(IField $Field) {
        $this->mField = $Field;
    }

    /**
     * @return IField
     */
    public function getField() {
        return $this->mField;
    }

    /**
     * Get the field name.
     * @return string
     * @throws \Exception if the name was never set.
     */
    function getName() {
        return $this->mField->getName();
    }

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    function validate(\CPath\Framework\Request\Interfaces\IRequest $Request, $fieldName) {
        return $this->mField->validate($Request, $fieldName);
    }


    /**
     * Internal function used to set the field name.
     * @param String $value
     * @return IField
     */
    function setValue($value) {
        return $this->mField->setValue($value);
    }


    /**
     * Returns the field flags
     * @return int
     */
    function getFieldFlags() {
        return $this->mField->getFieldFlags();
    }

    /**
     * @param $flags
     * @return bool
     */
    function hasFlags($flags) {
        return $this->mField->getFieldFlags() & $flags
            ? true
            : false;
    }

    function isRequired() { return $this->hasFlags(IField::IS_REQUIRED); }
    function isParam() { return $this->hasFlags(IField::IS_PARAMETER); }
}