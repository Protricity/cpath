<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Templates\User;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Builders\Tables\BuildPDOPKTable;
use CPath\Framework\PDO\Builders\Tables\BuildPHPTableClass;
use CPath\Framework\PDO\Columns\Template\PDOSimpleColumnTemplate as SimpleColumn;
use CPath\Framework\PDO\Templates\User\Model\PDOUserSessionModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserSessionTable;

class BuildPDOUserSessionTable extends BuildPDOPKTable {
    public $SessionExpireDays, $SessionExpireSeconds, $SessionKey, $SessionKeyLength;

    /**
     * Create a new BuildPDOUserSessionTable builder instance
     * @param String $name the table name
     * @param String $comment the table comment
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     */
    public function __construct($name, $comment, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($name, $comment,
            $PDOTableClass ?: PDOUserSessionTable::cls(),
            $PDOModelClass ?: PDOUserSessionModel::cls()
        );
        BuildPDOUserTable::addUserSessionTable($this);

        $this->addColumnTemplate(new SimpleColumn('key'));
        $this->addColumnTemplate(new SimpleColumn('user_id'));
        $this->addColumnTemplate(new SimpleColumn('expire'));
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
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Exceptions\BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {

    }
}