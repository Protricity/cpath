<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Field\Interfaces;
use CPath\Framework\API\Exceptions\ValidationException;
use CPath\Framework\Data\Collection\ICollectionItem;
use CPath\Framework\Request\Interfaces\IRequest;

/**
 * Class IField
 * @package CPath
 * Represents an API Field
 */
interface IField extends ICollectionItem {

    // Status
    const IS_REQUIRED           = 0x1;      // Field is required
    const IS_PARAMETER          = 0x2;      // Field is a path parameter

    /**
     * Get the field name.
     * @return string
     * @throws \Exception if the name was never set.
     */
    function getName();

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
     * @param String $value
     * @return IField
     */
    function setValue($value);
    /**
     * Returns the field flags
     * @return int
     */
    function getFieldFlags();
}