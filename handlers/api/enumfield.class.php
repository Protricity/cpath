<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;


class EnumField extends Field {
    protected $mEnum;
    public function __construct($description, $_enumValues) {
        parent::__construct($description);
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
            throw new ValidationException("Field '%s' must be one of the following: '" . implode("', '", $this->mEnum) . "'");
        return $value;
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return $this->getDescribable() . ": '" . implode("', '", $this->mEnum) . "'";
    }
}