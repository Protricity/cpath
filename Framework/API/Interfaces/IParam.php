<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Interfaces;
use CPath\Framework\Api\Field\Interfaces\IField;

/**
 * Class Param
 * @package CPath
 * This interface tags an API Field as a route parameter.
 */
interface IParam extends IField {

    /**
     * Returns true if this Field is a Param Field
     * @return bool
     */
    function isParam();
}
