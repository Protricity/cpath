<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Constructable;

final class Constructable {

    static function exportToPHPCode(IConstructable $Constructable, $constructorName=NULL) {
        $args = $Constructable->exportConstructorArgs();
        $class = get_class($Constructable);
        if($constructorName)
            $class = $constructorName;
        foreach($args as &$arg)
            $arg = var_export($arg, true);
        return "new " . $class . '(' . implode(', ', $args) . ')';
    }
}