<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Tables;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Table\Builders\AbstractBuildPDOPKTable;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Builders\Exceptions\TableArgumentNotFoundException;

class BuildPDOPKTable extends AbstractBuildPDOPKTable {

    /**
     * Create a new BuildPDOPKTable builder inst
     * @param \PDO $DB
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     * @internal param $namespace
     */
    public function __construct(\PDO $DB, $name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($DB, $name, $comment, $PDOTableClass, $PDOModelClass);
    }

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {}

    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws TableArgumentNotFoundException if the argument was not recognized
     */
    function processTableArg($arg) {
        throw new TableArgumentNotFoundException("Arg not found for table '" . $this->getTableName() . "': " . $arg);
    }
}