<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Serializer;

final class Serializer {

    public static function exportToPHPCode(ISerializable $Obj) {
        $export = $Obj->serialize();
        return get_class($Obj) . '::unserialize(' . var_export($export) .')'; // TODO: recursive serialize
    }

}
