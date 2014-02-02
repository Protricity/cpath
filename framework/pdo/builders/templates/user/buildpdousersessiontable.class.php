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
use CPath\Framework\PDO\Builders\Models\ArgumentNotFoundException;
use CPath\Framework\PDO\Builders\Tables\BuildPDOPKTable;
use CPath\Framework\PDO\Templates\User\Model\PDOUserSessionModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserSessionTable;

class BuildPDOUserSessionTable extends BuildPDOPKTable {
    public $Column_Key, $Column_User_ID, $Column_Expire;
    public $SessionExpireDays, $SessionExpireSeconds, $SessionKey, $SessionKeyLength;

    public function __construct($name, $comment) {
        \CPath\Framework\PDO\Builders\Tables\parent::__construct($name, $comment);
        BuildPDOUserTable::addUserSessionTable($this);
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
            case 'ck':
            case 'columnkey':
                $this->Column_Key = $this->req($name, $arg);
                break;
            case 'cui':
            case 'columnuserid':
                $this->Column_User_ID = $this->req($name, $arg);
                break;
            case 'ce':
            case 'columnexpire':
                $this->Column_Expire = $this->req($name, $arg);
                break;
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
        $TablePHP->setExtend(PDOUserSessionTable::cls());
        $ModelPHP->setExtend(PDOUserSessionModel::cls());


        foreach($this->getColumns() as $Column) {
            if(!$this->Column_User_ID && preg_match('/user.*id/i', $Column->Name))
                $this->Column_User_ID = $Column->Name;
            if(!$this->Column_Expire && stripos($Column->Name, 'expire') !== false)
                $this->Column_Expire = $Column->Name;
            if(!$this->Column_Key && stripos($Column->Name, 'key') !== false)
                $this->Column_Key = $Column->Name;
        }
        if(!$this->Column_Key && $this->Primary)
            $this->Column_Key = $this->Primary;

        foreach(array('Column_Key', 'Column_User_ID', 'Column_Expire') as $field) {
            if(!$this->$field)
                throw new BuildException("The field name for {$field} could not be determined for ".__CLASS__);
            $TablePHP->addConst(strtoupper($field), $this->$field);
        }

    }
}