<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Interfaces\IDescribable;
use CPath\Validate;



/**
 * Class RequiredFieldException
 * @package CPath
 * Throw when a required field is missing
 */
class RequiredFieldException extends ValidationException {
    function __construct($msg = "Field '%s' is required") {
        parent::__construct($msg);
    }
}

/**
 * Class APIRequiredField
 * @package CPath
 * Represents a 'required' API Field
 */
class RequiredField extends Field {
    public function validate($value) {
        $value = parent::validate($value);
        if(!$value && $value !== '0')
            throw new RequiredFieldException();
        return $value;
    }
}