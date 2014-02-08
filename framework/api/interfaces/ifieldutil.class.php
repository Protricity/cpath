<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Interfaces;

/**
 * Class IField
 * @package CPath
 * Represents an API Field
 */
interface IFieldUtil extends IField {

    /**
     * Returns true if any flag matches
     * @param $flags
     * @return bool
     */
    function hasFlags($flags);
}