<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Table\Builders\Templates\User;

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
    private $mSessionClass, $mRoleClass;

    /** @var BuildPDOUserSessionTable[] */
    protected static $mSessionTables = array();

    /** @var BuildPDOUserRoleTable[] */
    protected static $mRoleTables = array();

    /** @var BuildPDOTaskReceiptTable[] */
    protected static $mReceiptTables = array();

    /**
     * Create a new BuildPDOUserSessionTable builder inst
     * @param \PDO $DB
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     * @internal param null|String $namespace
     */
    public function __construct(\PDO $DB, $name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($DB, $name, $comment,
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
            case 'sc':
            case 'sessionclass':
                $this->mSessionClass = $this->req($name, $arg);
                break;
            default:
                throw new \InvalidArgumentException("User Argument not found: " . $field);
        }
    }

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Build\Exceptions\BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {

        if(!$this->mSessionClass) {
            foreach(self::$mSessionTables as $STable)
                if($STable->getNamespace() == $this->getNamespace()) {
                    $this->mSessionClass = $STable->getNamespace() . '\\' . $STable->getModelClass();
                    break;
                }
        }
        if(!$this->mSessionClass) {
            $this->mSessionClass = SimpleSession::cls();
            Log::e(__CLASS__, "Warning: No User session class found for UIElement '{$this->getTableTitle()}'. Defaulting to SimpleUserSession");
        }
        //$class = $this->Session_Class;
        //$Session = new $class;
        //if(!($Session instanceof IUserSession))
        //    throw new BuildException($class . " is not an inst of IUserSession");
        //$PHP->addConst('SESSION_CLASS', $class);
        $PHPTable->addUse($this->mSessionClass, 'Session');
        $PHPTable->addMethod('session', '', ' static $s = null; return $s ?: $s = new Session; ');

        if(!$this->mRoleClass) {
            foreach(self::$mRoleTables as $RTable)
                if($RTable->getNamespace() == $this->getNamespace()) {
                    $this->mRoleClass = $RTable->getNamespace() . '\\' . $RTable->getModelClass();
                    break;
                }
        }


        $Column = $this->getColumn('email');
        $Column->setFlag(PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT);
        if(!$Column->mFilter)
            $Column->mFilter = FILTER_VALIDATE_EMAIL;

        $Column = $this->getColumn('name');
        //if(!($Column->Flags & PDOColumn::FLAG_UNIQUE))
        //    Log::e(__CLASS__, "Warning: The user name Column '{$Column->Name}' may not have a unique constraint for UIElement '{$this->Name}'");
        $Column->setFlag(PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT);
        if(!$Column->mFilter)
            $Column->mFilter = Validate::FILTER_VALIDATE_USERNAME;

        $Column = $this->getColumn('password');
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
