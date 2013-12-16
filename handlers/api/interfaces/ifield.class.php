<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api\Interfaces;
use CPath\Interfaces\IDescribableAggregate;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;

/**
 * Class IField
 * @package CPath
 * Represents an API Field
 */
interface IField extends IHandler, IDescribableAggregate {
    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    function validate(IRequest $Request, $fieldName);

    /**
     * Internal function used to set the field name.
     * @param String $name
     * @return void
     * @throws \Exception if the name was already set.
     */
    function setName($name);

    /**
     * Internal function used to set the field name.
     * @param String $value
     * @return IField
     */
    function setValue($value);

    /**
     * Adds a short alias to the field.
     * @param String $shortName
     * @return void
     */
    function addShortName($shortName);

    /**
     * Returns a list of short names for this field.
     * @return Array returns an array of short names
     */
    function getShortNames();

    /**
     * Get the field name.
     * @return string
     * @throws \Exception if the name was never set.
     */
    function getName();

    /**
     * Returns true if this Field is a Param Field
     * @return bool
     */
    function isParam();

    /**
     * Returns true if this Field is a required Field
     * @return bool
     */
    function isRequired();
}