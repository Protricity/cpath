<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 7:10 AM
 */
namespace CPath\Request\Validation;

use CPath\Describable\IDescribable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Request\IRequest;

class RequiredField {
    private $mField;

    /**
     * Create a new API Field
     * @param $name
     * @param String|IDescribable|null $description
     * @param null $value
     */
    public function __construct($name, $description=null, $value=null) {
        $this->mField = new HTMLInputField('text', $value);
        $this->mField->setAttribute('name', $name);
    }

    /**
     * Render request form
     * @param IRequest $Request
     * @param IAttributes $Attr
     */
    function renderForm(IRequest $Request, IAttributes $Attr = null) {
        $this->mField->renderHTML($Request, $Attr);
    }

    /**
     * Validate this request parameter or throw an IRequestValidationException
     * @param IRequest $Request
     * @param $paramName
     * @return mixed the validated parameter value
     * @throws RequiredParameterException if the validation fails
     */
    function validateParameter(IRequest $Request, $paramName) {
        if(($value = $Request->getValue($paramName)) !== null) {
            $this->mField->setValue($value);
            return $value;
        }
        throw new RequiredParameterException($paramName, "Parameter is required: " . $paramName);
    }
}
