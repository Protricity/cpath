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
use CPath\Misc\RenderIndents as RI;


class EnumField extends Field {
    protected $mEnum;
    public function __construct($description, $_enumValues, $isRequired=false, $isParam=false) {
        parent::__construct($description, null, $isRequired, $isParam);
        $this->mEnum = is_array($_enumValues) ? $_enumValues : array_slice(func_get_args(), 1);
    }

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    function validate(IRequest $Request, $fieldName) {
        $value = parent::validate($Request, $fieldName);
        if(!in_array($value, $this->mEnum))
            throw new ValidationException("Value '{$value}' for field '%s' must be one of the following: '" . implode("', '", $this->mEnum) . "'");
        return $value;
    }

    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return parent::getDescribable() . ": '" . implode("', '", $this->mEnum) . "'";
    }

    /**
     * Render this input field as html
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request)
    {
        $value = $Request[$this->getName()];

        echo RI::ni(), "<select name='{$this->getName()}'>";
        foreach($this->mEnum as $enum)
            echo RI::ni(1), "<option value='{$enum}' selected='" . ($enum == $value ? 'selected' : '') . "'>{$enum}</option>";
        echo RI::ni(), "</select>";
    }
}