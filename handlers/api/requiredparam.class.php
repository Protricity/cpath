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
 * Class APIRquiredParam
 * @package CPath
 * Represents a Required Parameter from the route path
 */
class RequiredParam extends Field {
    /**
     * Create a new API Field
     * @param String|IDescribable $Description
     * @param int $validation
     */
    public function __construct($Description=NULL, $validation=0) {
        parent::__construct($Description, $validation, true, true);
    }
}
