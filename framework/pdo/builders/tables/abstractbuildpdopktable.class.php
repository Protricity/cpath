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
use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\PDOPrimaryKeyTable;

abstract class AbstractBuildPDOPKTable extends AbstractBuildPDOTable {

    /**
     * Create a new PDOPrimaryKeyTable builder instance
     * @param String $name the table name
     * @param String $comment the table comment
     */
    public function __construct($name, $comment)
    {
        parent::__construct($name, $comment,
            PDOPrimaryKeyTable::cls(),
            PDOPrimaryKeyModel::cls()
        );
    }

    /**
     * Additional processing for PHP classes for a PDO Primary Key Builder Template
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    abstract function processPKTemplatePHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP);

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP)
    {
        $ModelPHP->setExtend(PDOPrimaryKeyModel::cls());
        $TablePHP->setExtend(PDOPrimaryKeyTable::cls());
    }
}