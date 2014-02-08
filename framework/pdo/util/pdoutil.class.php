<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Util;

class PDOStringUtil{

    static function toTitleCase($field, $noSpace = false)
    {
        $field = preg_replace('/[^a-zA-Z0-9]/', ' ', $field);
        $field = ucwords($field);
        $words = explode(' ', $field);
        foreach ($words as &$word) {
            if (strlen($word) === 2)
                $word = strtoupper($word);
        }
        if (!$noSpace) return implode(' ', $words);;
        return implode('', $words);
    }
}

