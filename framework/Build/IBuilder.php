<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Build;

interface IBuilder {
    //const BUILD_IGNORE = trueRoute;

    /**
     * Performs a build on a class. If the class is not a type that should be built,
     * this method should return false immediately
     * @param IBuildable $Buildable
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\Exceptions\BuildException
     */
    function build(IBuildable $Buildable);

    /**
     * Executed when all classes have been built. Used to consolidate data.
     */
    function buildComplete();
}