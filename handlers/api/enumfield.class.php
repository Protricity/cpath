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


class EnumField extends Field {
    protected $mEnum;
    public function __construct($description, $_enumValues) {
        parent::__construct($description);
        $this->mEnum = is_array($_enumValues) ? $_enumValues : array_slice(func_get_args(), 1);
    }

    public function validate($value) {
        $value = parent::validate($value);
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