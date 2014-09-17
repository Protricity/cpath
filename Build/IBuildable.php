<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Build;
use CPath\Build\IBuildRequest;
use CPath\Exceptions\BuildException;
use CPath\Request\IRequest;

/**
 * Class IBuildable
 * A class that extends IBuildable is included in build requests
 * The class should return an instance of itself that will be used to build
 * @package CPath\Interfaces
 */

interface IBuildable {

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     * @build --disable 0
     * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
     */
    static function handleStaticBuild(IBuildRequest $Request);
}

