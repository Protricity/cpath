<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Field;

use CPath\Framework\API\Exceptions\ValidationException;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Attribute\IAttributes;
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
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);
        $Attr->add('type', 'password');
        parent::renderHTML($Request, $Attr);
    }
}