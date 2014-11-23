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
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;


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
     * @param IRequest $Request the request inst
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
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr=null) {
        $value = $Request[$this->getName()];

        echo RI::ni(), "<select name='{$this->getName()}'>";
        foreach($this->mEnum as $enum)
            echo RI::ni(1), "<option value='{$enum}' selected='" . ($enum == $value ? 'selected' : '') . "'>{$enum}</option>";
        echo RI::ni(), "</select>";
    }
}