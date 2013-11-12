<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\Interfaces\IRequiredField;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Interfaces\IDescribable;
use CPath\Validate;


/**
 * Class APIRequiredField
 * @package CPath
 * Represents a 'required' API Field
 */
class RequiredField extends Field implements IRequiredField {
}