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
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\User\Session\SimpleSession;
use CPath\Log;
use CPath\Validate;

class BuildPDOUserTable extends AbstractBuildPDOPKTable {
    public $Column_ID, $Column_Username, $Column_Email, $Column_Password, $Column_Flags;
    public $Session_Class, $Role_Class;
    /** @var BuildPDOUserSessionTable[] */
    protected static $mSessionTables = array();

    /** @var BuildPDOUserRoleTable[] */
    protected static $mRoleTables = array();

    /** @var BuildPDOTaskReceiptTable[] */
    protected static $mReceiptTables = array();

    /**
     * Process unrecognized table comment arguments
     * @param String $field the argument to process
     * @return void
     * @throws ArgumentNotFoundException if the argument was not recognized
     */
    function processTableArg($field) {
        list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
        switch(strtolower($name)) {
            case 'ci':
            case 'columnid':
                $this->Column_ID = $this->req($name, $arg);
                break;
            case 'cu':
            case 'columnusername':
                $this->Column_Username = $this->req($name, $arg);
                break;
            case 'ce':
            case 'columnemail':
                $this->Column_Email = $this->req($name, $arg);
                break;
            case 'cp':
            case 'columnpassword':
                $this->Column_Password = $this->req($name, $arg);
                break;
            case 'cf':
            case 'columnflags':
                $this->Column_Flags = $this->req($name, $arg);
                break;
            case 'sc':
            case 'sessionclass':
                $this->Session_Class = $this->req($name, $arg);
                break;
            default:
                \CPath\Framework\PDO\Builders\Tables\parent::processTableArg($field);
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
        $TablePHP->setExtend(PDOUserTable::cls());
        $ModelPHP->setExtend(PDOUserModel::cls());

        if(!$this->Session_Class) {
            foreach(Models\self::$mSessionTables as $STable)
                if($STable->Namespace == $this->Namespace) {
                    $this->Session_Class = $STable->Namespace . '\\' . $STable->ModelClassName;
                    break;
                }
        }
        if(!$this->Session_Class) {
            $this->Session_Class = SimpleSession::cls();
            Log::e(__CLASS__, "Warning: No User session class found for Table '{$this->Name}'. Defaulting to SimpleUserSession");
        }
        //$class = $this->Session_Class;
        //$Session = new $class;
        //if(!($Session instanceof IUserSession))
        //    throw new BuildException($class . " is not an instance of IUserSession");
        //$PHP->addConst('SESSION_CLASS', $class);
        $TablePHP->addUse($this->Session_Class);
        $TablePHP->addMethod('session', '', 'static $s = null; return $s ?: $s = new ' . basename($this->Session_Class) . ';');


        if(!$this->Role_Class) {
            foreach(Models\self::$mRoleTables as $RTable)
                if($RTable->Namespace == $this->Namespace) {
                    $this->Role_Class = $RTable->Namespace . '\\' . $RTable->ModelClassName;
                    break;
                }
        }




        if(!$this->Column_ID && $this->Primary) $this->Column_ID = $this->Primary;
        foreach($this->getColumns() as $Column) {
            if(!$this->Column_Username && stripos($Column->Name, 'name') !== false)
                $this->Column_Username = $Column->Name;
            if(!$this->Column_Email && stripos($Column->Name, 'mail') !== false)
                $this->Column_Email = $Column->Name;
            if(!$this->Column_Password && stripos($Column->Name, 'pass') !== false)
                $this->Column_Password = $Column->Name;
            if(!$this->Column_Flags && stripos($Column->Name, 'flag') !== false)
                $this->Column_Flags = $Column->Name;
        }

        foreach(array('Column_ID', 'Column_Username', 'Column_Email', 'Column_Password', 'Column_Flags') as $field) {
            if(!$this->$field)
                throw new BuildException("The column name for {$field} could not be determined for ".__CLASS__);
            $TablePHP->addConst(strtoupper($field), $this->$field);
        }

        $Column = $this->getColumn($this->Column_Email);
        $Column->Flags |= PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT;
        if(!$Column->Filter)
            $Column->Filter = FILTER_VALIDATE_EMAIL;

        $Column = $this->getColumn($this->Column_Username);
        //if(!($Column->Flags & PDOColumn::FLAG_UNIQUE))
        //    Log::e(__CLASS__, "Warning: The user name Column '{$Column->Name}' may not have a unique constraint for Table '{$this->Name}'");
        $Column->Flags |= PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT;
        if(!$Column->Filter)
            $Column->Filter = Validate::FILTER_VALIDATE_USERNAME;

        $Column = $this->getColumn($this->Column_Password);
        $Column->Flags |= PDOColumn::FLAG_PASSWORD | PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT;
        if(!$Column->Filter)
            $Column->Filter = Validate::FILTER_VALIDATE_PASSWORD;
    }

    public static function addUserSessionTable(BuildPDOUserSessionTable $Table) {
        self::$mSessionTables[] = $Table;
    }

    public static function addUserRoleTable(BuildPDOUserRoleTable $Table) {
        self::$mRoleTables[] = $Table;
    }

    public static function addTaskReceiptTable(BuildPDOTaskReceiptTable $Table) {
        self::$mReceiptTables[] = $Table;
        // TODO: Unused
    }
}
