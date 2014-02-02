<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Tables;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Exceptions\BuildException;

class BuildPDOPKTable extends AbstractBuildPDOPKTable {

    /**
     * Additional processing for PHP classes for a PDO Builder Primary Key Template
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processPKTemplatePHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP) {}

    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws TableArgumentNotFoundException if the argument was not recognized
     */
    function processTableArg($arg) {
        throw new TableArgumentNotFoundException("Arg not found for table '" . $this->Name . "': " . $arg);
    }
}