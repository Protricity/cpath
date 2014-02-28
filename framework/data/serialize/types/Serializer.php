<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Serialize\Types;

use CPath\Framework\Data\Serialize\Interfaces\ISerializable;

final class Serializer {
    private $mObject;

    function __construct($Object) {
        $this->mObject = $Object;
    }

    public static function exportToPHPCode(ISerializable $Obj) {
        $export = $Obj->serialize();
        if(is_string($export))
            return $export;
        return get_class($Obj) . '::unserialize(' . var_export($export, true) .')'; // TODO: recursive serialize
    }

    public static function exportToConstructor(ISerializable $Obj, Array $params) {
        $php = 'new ' . get_class($Obj)
            . '(';
        foreach(array_values($params) as $i => $arg)
            $php .= ($i ? ', ' : '') . var_export($arg, true);
        return $php . ')';
    }
}
