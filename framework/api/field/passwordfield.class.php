<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Field;

use CPath\Framework\Api\Interfaces\IField;
use CPath\Framework\Api\Exceptions\ValidationException;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Model\FileUpload;

/**
 * Class PasswordField
 * @package CPath
 */
class PasswordField extends RequiredField {

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return FileUpload|Array an instance of the file upload data or an array of instances
     * @throws ValidationException if validation fails
     */
    function validate(IRequest $Request, $fieldName) {
        return parent::validate($Request, $fieldName);
    }

    /**
     * Render this input field as html
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request) {
        parent::render($Request, array(
            'type' => 'password',
        ));
    }
}