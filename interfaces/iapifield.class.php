<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

/**
 * Class IApiField
 * @package CPath
 * Represents an API Field
 */
interface IApiField {
    public function validate($value);
}