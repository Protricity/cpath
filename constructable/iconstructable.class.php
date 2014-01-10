<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Constructable;

interface IConstructable {

    /**
     * Exports constructor parameters for code generation
     * @return Array constructor params for var_export
     */
    function exportConstructorArgs();
}
