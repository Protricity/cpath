<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;


class Parse {
    static function alpha($value) {
        return preg_replace('/[^a-z]/i', '', $value);
    }

    static function alphaNumeric($value) {
        return preg_replace('/[^a-z0-9]/i', '', $value);
    }

    static function title($value) {
        return preg_replace('/[^a-zA-Z0-9 _-]/', '', $value);
    }
}