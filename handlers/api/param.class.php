<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Interfaces\IDescribable;


/**
 * Class Param
 * @package CPath
 * Represents a Parameter from the route path
 */
class Param extends Field {

    /**
     * Create a new API Field
     * @param String|IDescribable $Description
     * @param int $validation
     * @param bool $isRequired
     */
    public function __construct($Description=NULL, $validation=0, $isRequired=false) {
        parent::__construct($Description, $validation, $isRequired, true);
    }
}