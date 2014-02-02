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
use CPath\Framework\PDO\Builders\Models\ArgumentNotFoundException;
use CPath\Framework\PDO\Templates\User\Role\Model\PDOUserRoleModel;
use CPath\Framework\PDO\Templates\User\Role\Table\PDOUserRoleTable;

class BuildPDOUserRoleTable extends AbstractBuildPDOPKTable {
    public $Column_User_ID, $Column_Class, $Column_Data;

    public function __construct($name, $comment) {
        \CPath\Framework\PDO\Builders\Tables\parent::__construct($name, $comment);
        BuildPDOUserTable::addUserRoleTable($this);
    }

    /**
     * Process unrecognized table comment arguments
     * @param String $field the argument to process
     * @return void
     * @throws ArgumentNotFoundException if the argument was not recognized
     */
    function processTableArg($field) {
        list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
        switch(strtolower($name)) {
            case 'cui':
            case 'columnuserid':
                $this->Column_User_ID = $this->req($name, $arg);
                break;
            case 'cd':
            case 'columndata':
                $this->Column_Data = $this->req($name, $arg);
                break;
            case 'cr':
            case 'columnrole':
                $this->Column_Class = $this->req($name, $arg);
                break;
        }
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
            if(!$this->Column_Class && stripos($Column->Name, 'class') !== false)
                $this->Column_Class = $Column->Name;
        }

        foreach(get_object_vars($this) as $field => $value) {
            if(!$value)
                throw new BuildException("The field name for {$field} could not be determined for ".__CLASS__);
            $TablePHP->addConst(strtoupper($field), $value);
        }

    }
}