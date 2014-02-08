<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Tables;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\DB\PDODatabase;
use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\PDOPrimaryKeyTable;

abstract class AbstractBuildPDOPKTable extends AbstractBuildPDOTable {

    /**
     * Create a new PDOPrimaryKeyTable builder instance
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     */
    public function __construct($name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($name, $comment,
            $PDOTableClass ?: PDOPrimaryKeyTable::cls(),
            $PDOModelClass ?: PDOPrimaryKeyModel::cls()
        );
    }

    /**
     * Process PHP classes for a PDO Builder
     * @param PDODatabase $DB
     * @param BuildPDOTable $Table
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Exceptions\BuildException
     * @return void
     */
    function processPHP(PDODatabase $DB, BuildPDOTable $Table, BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {
        $PHPTable->addConst('PRIMARY', $Table->Primary);
        parent::processPHP($DB, $Table, $PHPTable, $PHPModel);
    }
}