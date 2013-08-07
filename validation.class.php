<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

/**
 * Class Util provides information about the current request
 * @package CPath
 */
abstract class Validation {
    const FIELD_USERNAME = 1001;

    static function filter_var($variable, $filter, $options) {
        switch($variable) {
            case self::FIELD_USERNAME:
        }
        filter_var($variable, $filter, $options);
    }
}
