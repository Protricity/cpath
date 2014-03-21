<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Build;
use CPath\Exceptions\BuildException;

/**
 * Class IBuildable
 * A class that extends IBuildable is included in build requests
 * The class should return an instance of itself that will be used to build
 * @package CPath\Interfaces
 */

interface IBuildable {

    /**
     * Build this class
     * @throws BuildException if an exception occurred
     */
    static function buildClass();
}