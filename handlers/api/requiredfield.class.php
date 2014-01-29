<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;


/**
 * Class APIRequiredField
 * @package CPath
 * Represents a 'required' API Field
 */
class RequiredField extends Field {
    /**
     * Create a new API Field
     * @param String|\CPath\Describable\IDescribable $Description
     * @param int $validation
     * @param bool $isParam
     */
    public function __construct($Description=NULL, $validation=0, $isParam=false) {
        parent::__construct($Description, $validation, true, $isParam);
    }
}