<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Field;

use CPath\Describable\IDescribable;
use CPath\Framework\API\Exceptions\ValidationException;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;


class EnumField extends Field {
    protected $mEnum;

    /**
     * Create a new API Field
     * @param $name
     * @param String|IDescribable $Description
     * @param int $validation
     * @param int $flags
     * @param Array $_enumValues
     */
    public function __construct($name, $Description=NULL, $validation=0, $flags=0, $_enumValues) {
        parent::__construct($name, $Description, $validation, $flags);
        $this->mEnum = is_array($_enumValues) ? $_enumValues : array_slice(func_get_args(), 2);
    }

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws \CPath\Framework\API\Exceptions\ValidationException if validation fails
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