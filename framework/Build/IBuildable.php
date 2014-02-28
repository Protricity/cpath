<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Build;

/**
 * Class IBuildable
 * A class that extends IBuildable is included in build requests
 * The class should return an instance of itself that will be used to build
 * @package CPath\Interfaces
 */

interface IBuildable {

    /**
     * Return an instance of the class for building and other tasks
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance();
}