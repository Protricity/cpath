<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;


class Parse {

    /**
     * Removes all non alpha characters from a string
     * @param $value string the string to parse
     * @return string the parsed string
     */
    static function alpha($value) {
        return preg_replace('/[^a-z]/i', '', $value);
    }

    /**
     * Removes all non alphanumeric characters from a string
     * @param $value string the string to parse
     * @return string the parsed string
     */
    static function alphaNumeric($value) {
        return preg_replace('/\W|_/', '', $value);
    }

    /**
     * Removes all non alphanumeric, space, underscore, or dash characters from a string
     * @param $value string the string to parse
     * @return string the parsed string
     */
    static function title($value) {
        return preg_replace('/[^a-zA-Z0-9 _-]/', '', $value);
    }
}