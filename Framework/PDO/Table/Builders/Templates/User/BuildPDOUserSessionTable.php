<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Table\Builders\Templates\User;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Builders\Tables\BuildPDOPKTable;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Column\Template\Types\PDOSimpleColumnTemplate;
use CPath\Framework\PDO\Templates\User\Model\PDOUserSessionModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserSessionTable;

class BuildPDOUserSessionTable extends BuildPDOPKTable {
    public $SessionExpireDays, $SessionExpireSeconds, $SessionKey, $SessionKeyLength;

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
            $PDOTableClass ?: PDOUserSessionTable::cls(),
            $PDOModelClass ?: PDOUserSessionModel::cls()
        );
        BuildPDOUserTable::addUserSessionTable($this);

        $this->addColumnTemplate(new PDOSimpleColumnTemplate('key'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('user_id'));
        $this->addColumnTemplate(new PDOSimpleColumnTemplate('expire'));
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
            case 'sk':
            case 'sessionkey':
                $this->SessionKey = $this->req($name, $arg);
                break;
            case 'sed':
            case 'sessionexpiredays':
                $this->SessionExpireDays = $this->req($name, $arg);
                break;
            case 'ses':
            case 'sessionexpireseconds':
                $this->SessionExpireSeconds = $this->req($name, $arg);
                break;
            case 'skl':
            case 'sessionkeylength':
                $this->SessionExpireDays = $this->req($name, $arg);
                break;
            default:
                throw new \InvalidArgumentException("User Session Argument not found: " . $field);
        }
    }

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param \CPath\Framework\PDO\Table\Builders\BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Exceptions\BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {

//        $PHPModel->addUse($PHPTable->getName(true), 'Table');
//        $PHPModel->addMethod('session', '', ' static $table=null; return $table ?: $table = new Table; ');
//        $PHPModel->addMethodCode();
    }
}