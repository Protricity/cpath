<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\Interfaces\IRequiredField;
use CPath\Handlers\Api\Interfaces\RequiredFieldException;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Model\FileUpload;
use CPath\Validate;

/**
 * Class FileUploadField
 * @package CPath
 * Represents a 'required' API Field
 */
class FileUploadField extends Field {
    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return FileUpload|Array an instance of the file upload data or an array of instances
     * @throws ValidationException if validation fails
     * @throws RequiredFieldException if a required field has no value
     */
    function validate(IRequest $Request, $fieldName) {
        $File = $Request->getFileUpload($fieldName);
        if(!$File && $this instanceof IRequiredField)
            throw new RequiredFieldException();
        return $File;
    }

    /**
     * Render this input field as html
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request)
    {
        echo "<input type='file' name='{$this->getName()}' placeholder='Enter value for {$this->getName()}' />";
    }
}