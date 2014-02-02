<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:30 AM
 */
namespace CPath\Framework\PDO\Builders\Columns;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Exceptions\BuildException;

class BuildPDOColumn extends AbstractBuildPDOColumn
{
    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws ColumnArgumentNotFoundException if the argument was not recognized
     */
    function processColumnArg($arg) {}

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP) {}
}