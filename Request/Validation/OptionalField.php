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
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Request\IRequest;

class OptionalField implements IValidateRequest
{
    private $mField;
    private $mName;

    /**
     * Create a new API Field
     * @param $name
     * @param String|IDescribable|null $description
     * @param mixed|null $value
     */
    public function __construct($name, $description = NULL, $value = null) {
        $this->mField = new HTMLInputField('text', $value);
        $this->mField->setAttribute('name', $name);
        $this->mName = $name;
    }
    /**
     * Validate this request or throw a ValidationException
     * @param IRequest $Request
     * @return mixed
     * @throws ValidationException if the validation fails
     */
    function validateRequest(IRequest $Request) {
        $name = $this->mName;
        if(($value = $Request->getValue($name)) !== null) {
            $this->mField->setValue($value);
        }
    }

    /**
     * @param IRequest $Request
     * @param IAttributes $Attr
     * @return mixed
     */
    function renderForm(IRequest $Request, IAttributes $Attr = null) {
        $this->mField->renderHTML($Request, $Attr);
    }
}
