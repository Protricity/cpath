<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Table\Builders\Templates\User;

use CPath\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Table\Builders\AbstractBuildPDOPKTable;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Builders\Templates\Task\BuildPDOTaskReceiptTable;
use CPath\Framework\PDO\Table\Column\Template\Types\PDOSimpleColumnTemplate;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
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
     * Create a new BuildPDOUserSessionTable builder instance
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     */
    public function __construct($name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($name, $comment,
            $PDOTableClass ?: PDOUserTable::cls(),
            $PDOModelClass ?: PDOUserModel::cls()
        );
        //BuildPDOUserTable::addUserSessionTable($this);

        $this->addColumnTemplate(new PDOSimpleColumnTemplate('id'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('user_name'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('email'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('password'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('flags'));
    }

    /**
     * Process unrecognized table comment arguments
     * @param String $field the argument to process
     * @return void
     * @throws \InvalidArgumentException if the argument was not recognized
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
                throw new \InvalidArgumentException("User Argument not found: " . $field);
        }
    }

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Exceptions\BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {
        $PHPTable->setExtend(PDOUserTable::cls());
        $PHPModel->setExtend(PDOUserModel::cls());

        if(!$this->Session_Class) {
            foreach(Models\self::$mSessionTables as $STable)
                if($STable->getNamespace() == $this->getNamespace()) {
                    $this->Session_Class = $STable->$this->getNamespace() . '\\' . $STable->getModelClass();
                    break;
                }
        }
        if(!$this->Session_Class) {
            $this->Session_Class = SimpleSession::cls();
            Log::e(__CLASS__, "Warning: No User session class found for Table '{$this->getTableTitle()}'. Defaulting to SimpleUserSession");
        }
        //$class = $this->Session_Class;
        //$Session = new $class;
        //if(!($Session instanceof IUserSession))
        //    throw new BuildException($class . " is not an instance of IUserSession");
        //$PHP->addConst('SESSION_CLASS', $class);
        $PHPTable->addUse($this->Session_Class);
        $PHPTable->addMethod('session', '', 'static $s = null; return $s ?: $s = new ' . basename($this->Session_Class) . ';');


        if(!$this->Role_Class) {
            foreach(Models\self::$mRoleTables as $RTable)
                if($RTable->getNamespace() == $this->getNamespace()) {
                    $this->Role_Class = $RTable->getNamespace() . '\\' . $RTable->getModelClass();
                    break;
                }
        }




        if(!$this->Column_ID)
            $this->Column_ID = $this->getPrimaryKeyColumn();

        foreach($this->getColumns() as $Column) {
            if(!$this->Column_Username && stripos($Column->getName(), 'name') !== false)
                $this->Column_Username = $Column->getName();
            if(!$this->Column_Email && stripos($Column->getName(), 'mail') !== false)
                $this->Column_Email = $Column->getName();
            if(!$this->Column_Password && stripos($Column->getName(), 'pass') !== false)
                $this->Column_Password = $Column->getName();
            if(!$this->Column_Flags && stripos($Column->getName(), 'flag') !== false)
                $this->Column_Flags = $Column->getName();
        }

        foreach(array('Column_ID', 'Column_Username', 'Column_Email', 'Column_Password', 'Column_Flags') as $field) {
            if(!$this->$field)
                throw new BuildException("The column name for {$field} could not be determined for ".__CLASS__);
            $PHPTable->addConst(strtoupper($field), $this->$field);
        }

        $Column = $this->getColumn($this->Column_Email);
        $Column->setFlag(PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT);
        if(!$Column->mFilter)
            $Column->mFilter = FILTER_VALIDATE_EMAIL;

        $Column = $this->getColumn($this->Column_Username);
        //if(!($Column->Flags & PDOColumn::FLAG_UNIQUE))
        //    Log::e(__CLASS__, "Warning: The user name Column '{$Column->Name}' may not have a unique constraint for Table '{$this->Name}'");
        $Column->setFlag(PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT);
        if(!$Column->mFilter)
            $Column->mFilter = Validate::FILTER_VALIDATE_USERNAME;

        $Column = $this->getColumn($this->Column_Password);
        $Column->setFlag(PDOColumn::FLAG_PASSWORD | PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT);
        if(!$Column->mFilter)
            $Column->mFilter = Validate::FILTER_VALIDATE_PASSWORD;
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
