<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Describable\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Model\FileUpload;

/**
 * Class PasswordField
 * @package CPath
 */
class PasswordField extends Field {

    /**
     * Create a new API Field
     * @param String|\CPath\Describable\IDescribable $Description
     * @param int $validation
     * @param bool $isRequired
     * @param bool $isParam
     */
    public function __construct($Description=NULL, $validation=0, $isRequired=true, $isParam=false) {
        parent::__construct($Description, $validation, $isRequired, $isParam);
    }

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