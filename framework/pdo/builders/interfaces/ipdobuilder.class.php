<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Model\Helpers;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Exceptions\BuildException;

interface IPDOBuilder {

    /**
     * Process PHP classes for a PDO Builder
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processPHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP);
}

