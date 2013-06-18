<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IBuilder {
    //const BUILD_IGNORE = trueRoute;

    static function build(\ReflectionClass $Class);
    static function buildComplete(\ReflectionClass $Class);
}