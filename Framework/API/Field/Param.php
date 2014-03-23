<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Field;
use CPath\Describable\IDescribable;

/**
 * Class Param
 * @package CPath
 * Represents a Parameter from the route path
 */
class Param extends Field {
    /**
     * Create a new API Field
     * @param $name
     * @param String|IDescribable $Description
     * @param int $validation
     * @param int $flags
     */
    public function __construct($name, $Description=NULL, $validation=0, $flags=0) {
        parent::__construct($name, $Description, $validation, $flags | Interfaces\IField::IS_PARAMETER);
    }
}