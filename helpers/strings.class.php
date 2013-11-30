<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Helpers;


final class Strings {

    public static function truncate($string, $length=128, $append='...', $cut=false) {

        if(strlen($string) > $length) {

            $string = wordwrap(substr($string, 0, $length), intval($length * 0.9), "\n", $cut);

            if(($p = strpos($string, "\n", intval($length * 0.75))) !== false)
                $string = substr($string, 0, $p);

            $string .= $append;
        }
        return $string;
    }

}
