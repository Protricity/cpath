<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Templates\User;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Builders\Tables\AbstractBuildPDOPKTable;
use CPath\Framework\PDO\Builders\Tables\BuildPHPTableClass;
use CPath\Framework\PDO\Columns\Template\PDOSimpleColumnTemplate as SimpleColumn;
use CPath\Framework\PDO\Templates\User\Role\Model\PDOUserRoleModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;

class BuildPDOTaskReceiptTable extends AbstractBuildPDOPKTable {
    public $Column_User_ID, $Column_Class, $Column_Data;

    /**
     * Create a new BuildPDOUserSessionTable builder instance
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     */
    public function __construct($name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($name, $comment,
            $PDOTableClass ?: PDOUserRoleTable::cls(),
            $PDOModelClass ?: PDOUserRoleModel::cls()
        );
        BuildPDOUserTable::addTaskReceiptTable($this);

        $this->addColumnTemplate(new SimpleColumn('user_id'));
        $this->addColumnTemplate(new SimpleColumn('class'));
        $this->addColumnTemplate(new SimpleColumn('data'));
    }


    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Exceptions\BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {
    }

    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @throws \InvalidArgumentException
     * @return void
     */
    function processTableArg($arg) {
        throw new \InvalidArgumentException("Task Argument not found: " . $arg);
    }
}