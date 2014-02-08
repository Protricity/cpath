<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:30 AM
 */
namespace CPath\Framework\PDO\Builders\Columns;

use CPath\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Builders\Tables\BuildPHPTableClass;

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
     * @param BuildPHPTableClass $TablePHP
     * @param BuildPHPModelClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $TablePHP, BuildPHPModelClass $ModelPHP) {}
}