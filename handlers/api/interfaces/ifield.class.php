<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api\Interfaces;
use CPath\Interfaces\IDescribableAggregate;

/**
 * Class IField
 * @package CPath
 * Represents an API Field
 */
interface IField extends IDescribableAggregate {
    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param mixed $value the input field to validate
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    function validate($value);

    /**
     * Internal function used to set the field name.
     * @param String $name
     * @return void
     * @throws \Exception if the name was already set.
     */
    function setName($name);

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
}