<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Templates\User;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Tables\AbstractBuildPDOPKTable;
use CPath\Framework\PDO\Columns\Template\PDOSimpleColumnTemplate as SimpleColumn;
use CPath\Framework\PDO\Templates\User\Role\Model\PDOUserRoleModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;

class BuildPDOTaskReceiptTable extends AbstractBuildPDOPKTable {
    public $Column_User_ID, $Column_Class, $Column_Data;

    public function __construct($name, $comment) {
        parent::__construct($name, $comment);
        BuildPDOUserTable::addTaskReceiptTable($this);

        $this->addColumnTemplate(new SimpleColumn('user_id'));
        $this->addColumnTemplate(new SimpleColumn('class'));
        $this->addColumnTemplate(new SimpleColumn('data'));
    }

    /**
     * Additional processing for PHP classes for a PDO Primary Key Builder Template
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processPKTemplatePHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP) {
        $TablePHP->setExtend(PDOUserRoleTable::cls());
        $ModelPHP->setExtend(PDOUserRoleModel::cls());

        foreach($this->getColumns() as $Column) {
            if(!$this->Column_User_ID && preg_match('/user.*id/i', $Column->Name))
                $this->Column_User_ID = $Column->Name;
            if(!$this->Column_Data && stripos($Column->Name, 'data') !== false)
                $this->Column_Data = $Column->Name;
            if(!$this->Column_Class && stripos($Column->Name, 'role') !== false)
                $this->Column_Class = $Column->Name;
        }

        foreach(array('Column_Data', 'Column_User_ID', 'Column_Role_Class') as $field) {
            if(!$this->$field)
                throw new BuildException("The field name for {$field} could not be determined for ".__CLASS__);
            $TablePHP->addConst(strtoupper($field), $this->$field);
        }
    }
}