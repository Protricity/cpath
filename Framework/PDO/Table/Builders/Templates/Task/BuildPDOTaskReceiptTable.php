<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Table\Builders\Templates\Task;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Table\Builders\AbstractBuildPDOPKTable;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Builders\Templates\User\BuildPDOUserTable;
use CPath\Framework\PDO\Table\Column\Template\Types\PDOSimpleColumnTemplate;
use CPath\Framework\PDO\Templates\User\Role\Model\PDOUserRoleModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;

class BuildPDOTaskReceiptTable extends AbstractBuildPDOPKTable {
    public $Column_User_ID, $Column_Class, $Column_Data;

    /**
     * Create a new BuildPDOUserSessionTable builder instance
     * @param \PDO $DB
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     * @internal param null|String $namespace
     */
    public function __construct(\PDO $DB, $name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($DB, $name, $comment,
            $PDOTableClass ?: PDOUserRoleTable::cls(),
            $PDOModelClass ?: PDOUserRoleModel::cls()
        );
        BuildPDOUserTable::addTaskReceiptTable($this);

        $this->addColumnTemplate(new PDOSimpleColumnTemplate('user_id'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('class'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('data'));
    }


    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param \CPath\Framework\PDO\Table\Builders\BuildPHPTableClass $PHPTable
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