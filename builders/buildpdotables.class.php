<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\Interfaces\IUserSession;
use CPath\Model\DB\PDODatabase;
use CPath\BuildException;
use CPath\Base;
use CPath\Build;
use CPath\Interfaces\IBuilder;
use CPath\Log;
use CPath\Builders\Tools\BuildPHPClass;


class UpgradeException extends \Exception {}

abstract class BuildPDOTables implements IBuilder{
    const DB_CLASSNAME = "CPath\\Model\\DB\\PDODatabase";

    const TMPL_PROC_CLASS = <<<PHP
<?php
namespace %s;
class Procs {
%s}
PHP;

    const TMPL_PROC = <<<'PHP'
	static function %s(\PDO $DB%s) {
		static $stmd = NULL;
		if(!$stmd) $stmd = $DB->prepare('SELECT %s(%s)');
		$stmd->execute(array(%s));
		return $stmd;
	}
PHP;


    // TODO: move out of the build class
    public function upgrade(PDODatabase $DB, $oldVersion=NULL) {
        if($oldVersion===NULL)
            $oldVersion = $DB->getDBVersion();
        $curVersion = $DB::Version;
        $Class = new \ReflectionClass($DB);
        $schemaFolder = $this->getFolder($Class, 'schema');
        $files = scandir($schemaFolder);
        if(!$files)
            throw new UpgradeException("No Schemas found in ".$schemaFolder);
        $schemas = array();
        foreach($files as $i=>$file) {
            if(in_array($file, array('.', '..')))
                continue;
            if(!is_file($schemaFolder.'/'.$file))
                continue;
            $name = pathinfo($file, PATHINFO_FILENAME);
            if(!is_numeric($name))
                continue;
            //throw new UpgradeException("File '{$file}' is not numeric");
            $name = (int)$name;
            if($name <= $oldVersion)
                continue;
            $schemas[$name] = $file;
        }
        if(!$schemas)
            throw new UpgradeException("New Version Number, but no new schemas found");
        ksort($schemas);
        foreach($schemas as $v=>$schema) {
            $sql = file_get_contents($schemaFolder.'/'.$schema);
            if(!$sql)
                throw new UpgradeException("Invalid SQL in ".$schema);
            $DB->exec($sql);

            $DB->setDBVersion($v);
        }
        Log::v(__CLASS__, "Upgraded Database from version $oldVersion to $curVersion.");
    }

    /**
     * @param \PDO $DB
     * @return BuildPDOTable[]
     */
    protected abstract function getTables(\PDO $DB);

    /**
     * @param \PDO $DB
     * @param BuildPDOTable $Table
     * @return void
     */
    protected abstract function getColumns(\PDO $DB, BuildPDOTable $Table);

    /**
     * @param \PDO $DB
     * @param BuildPDOTable $Table
     * @return void
     */
    protected abstract function getIndexes(\PDO $DB, BuildPDOTable $Table);


    protected abstract function getProcs(\PDO $DB);

    /**
     * Builds class references for existing database tables
     * @param \ReflectionClass $Class
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\BuildException when a build exception occurred
     */
    public function build(\ReflectionClass $Class) {

        if(!$Class->isSubclassOf(static::DB_CLASSNAME))
            return false;

        $BUILD = $Class->getConstant('Build_DB');
        if(!in_array($BUILD, array('ALL', 'MODEL', 'PROC'))) {
            Log::v(__CLASS__, "(Build_DB = {$BUILD}) Skipping Build for ".$Class->getName());
            return false;
        }

        /* @var $DB PDODatabase */
        $DB = call_user_func(array($Class->getName(), 'get'));

        $modelPath = $this->getFolder($Class, 'model');
        $modelNS = $Class->getNamespaceName() . "\\Model";
        $procPath = $this->getFolder($Class, 'procs');
        $procNS = $Class->getNamespaceName() . "\\Procs";

        $Config =& Build::getConfig($Class->getName());
        $schemaFolder = $this->getFolder($Class, 'schema');
        $hash = 0;
        $force = Build::force();

        $oldFiles = array();
        if(!file_exists($procPath))  { mkdir($procPath, null, true);  $force = true; }
        if(!file_exists($modelPath)) { mkdir($modelPath, null, true); $force = true; }
        else $oldFiles = array_diff(scandir($modelPath), array('..', '.'));

        foreach(scandir($schemaFolder) as $file)
            $hash += filemtime($schemaFolder.$file);
        if(!$force && isset($Config['schemaHash']) && $Config['schemaHash'] == $hash) {
            Log::v(__CLASS__, "Skipping Build for ".$Class->getName());
            return false;
        }
        $Config['schemaHash'] = $hash;


        // Tables

        $tables = array();
        if(in_array($BUILD, array('ALL', 'MODEL')))
            $tables = $this->getTables($DB);

        foreach($tables as $Table) {
            $PHP = new BuildPHPClass($Table->ClassName);
            $PHP->Namespace = $modelNS;
            $Table->Namespace = $modelNS;

            $this->getColumns($DB, $Table);
            $this->getIndexes($DB, $Table);

            $file = $modelPath.strtolower($Table->ClassName).'.class.php';

            // Model
            $Table->processModelPHP($PHP);

            $PHP->addUse(get_class($DB), 'DB');

            $PHP->addConst('TableName', $Table->Name);
            if($Table->ModelName)
                $PHP->addConst('ModelName', $Table->ModelName);

            $PHP->addConst('Primary', $Table->Primary);

            $colNames = array();
            foreach($Table->getColumns() as $Column)
                $colNames[] = $Column->Name;
            $PHP->addConst('Columns', implode(',', $colNames));

            $comments = array();
            foreach($Table->getColumns() as $Column)
                $comments[] = $Column->Comment;
            $PHP->addConst('Comments', implode(';', $comments));

            $types = '';
            $InsertFields = array();
            $UpdateFields = array();
            $SearchFields = array();
            $ExportFields = array();

            foreach($Table->getColumns() as $Column) {
                if($Column->Insert) $InsertFields[] = $Column->Name;
                if($Column->Update) $UpdateFields[] = $Column->Name;
                if($Column->Search) $SearchFields[] = $Column->Name;
                if($Column->Export) $ExportFields[] = $Column->Name;
                $types .= $Column->Type;
            }

            if(!$InsertFields && $Table->Insert) $InsertFields[] = $Table->Insert;
            if(!$UpdateFields && $Table->Update) $UpdateFields[] = $Table->Update;
            if(!$SearchFields && $Table->Search) $SearchFields[] = $Table->Search;
            if(!$ExportFields && $Table->Export) $ExportFields[] = $Table->Export;

            if(!$SearchFields)
                foreach($Table->getColumns() as $Column)
                    if($Column->Index)
                        $SearchFields[] = $Column->Name;

            $PHP->addConst('Types', $types);

            if($InsertFields) $PHP->addConst('Insert', implode(',', $InsertFields));
            if($UpdateFields) $PHP->addConst('Update', implode(',', $UpdateFields));
            if($SearchFields) $PHP->addConst('Search', implode(',', $SearchFields));
            if($ExportFields) $PHP->addConst('Export', implode(',', $ExportFields));
            if($Table->SearchWildCard)
                $PHP->addConst('SearchWildCard', true);
            if($Table->SearchLimit)
                $PHP->addConst('SearchLimit', $Table->SearchLimit);
            if($Table->SearchLimitMax)
                $PHP->addConst('SearchLimitMax', $Table->SearchLimitMax);
            if($Table->AllowHandler)
                $PHP->addConst('Build_Ignore', false);

            $PHP->addConstCode();
            $PHP->addConstCode("// Field Constants ");
            foreach($Table->getColumns() as $Column)
                $PHP->addConst(strtoupper($Column->Name), $Column->Name);

            foreach($Table->getColumns() as $Column)
                $PHP->addProperty($Column->Name);

            foreach($Table->getColumns() as $Column) {
                $ucName = str_replace(' ', '', ucwords(str_replace('_', ' ', $Column->Name)));
                $PHP->addMethod('get' . $ucName, '', sprintf(' return $this->%s; ', strtolower($Column->Name)));
                if(!$Column->Primary)
                    $PHP->addMethod('set' . $ucName, '$value, $commit=true', sprintf(' return $this->setField(\'%s\', $value, $commit%s); ', strtolower($Column->Name), ($Column->Filter ? ', '.$Column->Filter : '')));
                if($Column->Enum) {
                    $a = '';
                    foreach($Column->Enum as $e)
                        $a .= ($a ? ',' : '') . var_export($e, true);
                    $PHP->addStaticMethod("get{$ucName}EnumValues", '', ' return array('.$a.'); ');
                }
                $PHP->addMethodCode();
            }

            $PHP->addStaticMethod('remove', $Table->ClassName . ' $' . $Table->ClassName, " parent::removeModel(\${$Table->ClassName}); ");
            $PHP->addStaticMethod('getDB', '', " return DB::get(); ");

            file_put_contents($file, $PHP->build());
            foreach($oldFiles as $i => $f)
                if($modelPath.$f == $file) {
                    unset($oldFiles[$i]);
                    break;
                }
        }
        //Log::v(__CLASS__, "Built (".sizeof($tables).") table definition class(es)");
        Log::v(__CLASS__, "Built (".sizeof($tables).") table model(s)");
        if($c = sizeof($oldFiles)) {
            Log::v(__CLASS__, "Removing ({$c}) depreciated model classes");
            foreach($oldFiles as $file) unlink($modelPath.$file);
        }

        // Stored Procedures

        $procs = array();
        if(in_array($BUILD, array('ALL', 'PROC')))
            $procs = $this->getProcs($DB);


        $phpC = '';
        $phpP = '';
        $names = array();
        foreach($procs as $proc) {
            $name = array_shift($proc);
            if(isset($names[$name])) {
                $name .= ++$names[$name];
            } else {
                $names[$name] = 1;
            }
            $method = $name.'('.(!$proc ? '' : ('%s'.str_repeat(', %s', sizeof($proc)-1))).')';
            $phpC .= self::getConst(strtoupper($name), $method);
            $phpP .= $this->getProc($name, $proc);
        }
        $php = sprintf(self::TMPL_PROC_CLASS, $procNS, $phpC.$phpP);
        file_put_contents($procPath.'procs.class.php', $php);
        Log::v(__CLASS__, "Built (".sizeof($procs).") routine(s)");

        return true;
    }

    /**
     * Unused
     */
    public function buildComplete() {

    }

    private function getFolder(\ReflectionClass $Class, $subFolder=NULL) {
        if($subFolder) $subFolder .= '/';
        return dirname($Class->getFileName()) . '/' . $subFolder;
    }

    static function getConst($name, $value) {
        return "\tconst {$name} = ".var_export($value, true).";\n";
    }

    private function getProc($name, $params) {
        return sprintf(self::TMPL_PROC,
            $name, !$params ? '' : ', $'.implode(', $', $params),
            $name, !$params ? '' : '?'.str_repeat(', ?', sizeof($params)-1),
            '$'.implode(', $', $params));
    }

}

class BuildPDOColumn {
    public $Name, $Type, $Comment, $AutoInc, $Primary, $Enum, $Index, $Insert, $Update, $Search, $Export, $Filter=NULL;

    public function __construct($name, $type, $comment, $autoInc=false) {
        if(is_array($type)) {
            $this->Enum = $type;
            $type = 'e';
        }
        $this->Name = $name;
        $this->Type = $type;
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace') , $comment);
        if(!$this->Comment)
            $this->Comment = $comment;
        if($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
        $this->AutoInc = $autoInc;
    }

    function replace(array $matches) {
        foreach(explode('|', $matches[1]) as $field) {
            $args = explode(':', $field, 2);
            switch(strtolower($args[0])) {
                case 'i':
                case 'insert':
                    $this->Insert = true;
                    break;
                case 'u':
                case 'update':
                    $this->Update = true;
                    break;
                case 's':
                case 'search':
                    $this->Search = true;
                    break;
                case 'e':
                case 'export':
                    $this->Export = true;
                    break;
                case 'c':
                case 'comment':
                    $this->Comment = $this->req($args);
                    break;
                case 'f':
                case 'filter':
                    $filter = $this->req($args);
                    if(!is_numeric($filter))
                        $filter = constant($filter);
                    $this->Filter |= (int)$filter;
                    break;
            }
        }
        return '';
    }

    private function req($args, $preg=NULL, $desc=NULL) {
        if(!isset($args[1]) || ($preg && !preg_match($preg, $args[1], $matches)))
            throw new BuildException("Column Comment Token {$args[0]} must be in the format {{$args[0]}:" . ($desc ?: $preg ?: 'value') . '}');
        if(!$preg)
            return $args[1];
        array_shift($matches);
        return $matches;
    }
}

class BuildPDOTable {

    public $Name, $Namespace, $ClassName, $ModelName, $Comment, $Insert, $Update, $Search, $Export,
        $SearchWildCard, $SearchLimit, $SearchLimitMax, $AllowHandler = false, $Primary, $Template;
    protected $mColumns = array();
    protected $mUnfound = array();

    protected function __construct($name, $comment) {
        $this->Name = $name;
        $this->ClassName = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->Name))) . "Model";
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace'), $comment);
        if(!$this->Comment)
            $this->Comment = $comment;
        if($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
    }

    function processDefault($field) {
        $this->mUnfound[] = $field;
    }

    function replace(array $matches) {
        foreach(explode('|', $matches[1]) as $field) {
            list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
            switch(strtolower($name)) {
                case 't':
                case 'template':
                    $this->Template = $this->req($name, $arg);
                    break;
                case 'i':
                case 'insert':
                    $this->Insert = $this->req($name, $arg);
                    break;
                case 'u':
                case 'update':
                    $this->Update = $this->req($name, $arg);
                    break;
                case 's':
                case 'search':
                    $this->Search = $this->req($name, $arg);
                    break;
                case 'sw':
                case 'searchwildcard':
                    $this->SearchWildCard = true;
                    break;
                case 'sl':
                case 'searchlimit':
                    list($this->SearchLimit, $this->SearchLimitMax) =
                        $this->req($name, $arg, '/^(\d+):(\d+)$/', '{default limit}:{max limit}');
                    break;
                case 'e':
                case 'export':
                    $this->Export = $this->req($name, $arg);
                    break;
                case 'c':
                case 'comment':
                    $this->Comment = $this->req($name, $arg);
                    break;
                case 'n':
                case 'name':
                    $this->ModelName = $this->req($name, $arg);
                    break;
                case 'ah':
                case 'api':
                case 'allowhandler':
                    $this->AllowHandler = true;
                    break;
                default:
                    $this->processDefault($field);
            }
        }
        return '';
    }

    /**
     * @return BuildPDOColumn[]
     */
    public function getColumns() {
        return $this->mColumns;
    }

    /**
     * @param $name
     * @return BuildPDOColumn
     */
    public function getColumn($name) {
        return $this->mColumns[$name];
    }

    public function addColumn(BuildPDOColumn $Column) {
        $this->mColumns[$Column->Name] = $Column;
    }

    function processModelPHP(BuildPHPClass $PHP) {
        $PHP->setExtend("CPath\\Model\\DB\\PDOModel");

        if($this->mUnfound)
            throw new BuildException("Invalid Table Comment Token '" . implode('|', $this->mUnfound) . "' in Table '{$this->Name}'");

        if(!$this->Primary)
            foreach($this->getColumns() as $Column) {
                if($Column->Primary) {
                    $this->Primary = $Column->Name;
                    break;
                }
            }

        if(!$this->Primary)
            Log::e(__CLASS__, "Warning: No Primary key found for Table '{$this->Name}'");
    }
    
    protected function req($name, $arg, $preg=NULL, $desc=NULL) {
        if(!$arg || ($preg && !preg_match($preg, $arg, $matches)))
            throw new BuildException("Table Comment Token {$name} must be in the format {{$name}:" . ($desc ?: $preg ?: 'value') . '}');
        if(!$preg)
            return $arg;
        array_shift($matches);
        return $matches;
    }

    static function create($name, $comment) {
        $Table = new BuildPDOTable($name, $comment);
        if($Table->Template) switch(strtolower($Table->Template)) {
            case 'u':
            case 'user':
                $Table = new BuildPDOUserTable($name, $comment);
                break;
            case 'us':
            case 'usersession':
                $Table = new BuildPDOUserSessionTable($name, $comment);
                break;
        }
        return $Table;
    }
}

class BuildPDOUserTable extends BuildPDOTable {
    public $FieldID, $FieldUsername, $FieldEmail, $FieldPassword, $FieldFlags, $SessionClass;
    /** @var BuildPDOUserSessionTable[] */
    protected static $mSessionTables = array();

    function defaultCommentArg($field) {
        list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
        switch(strtolower($name)) {
            case 'fi':
            case 'fieldid':
                $this->FieldID = $this->req($name, $arg);
                break;
            case 'fu':
            case 'fieldusername':
                $this->FieldUsername = $this->req($name, $arg);
                break;
            case 'fe':
            case 'fieldemail':
                $this->FieldEmail = $this->req($name, $arg);
                break;
            case 'fp':
            case 'fieldpassword':
                $this->FieldPassword = $this->req($name, $arg);
                break;
            case 'ff':
            case 'fieldflags':
                $this->FieldFlags = $this->req($name, $arg);
                break;
            case 'sc':
            case 'sessionclass':
                $this->SessionClass = $this->req($name, $arg);
                break;
            default:
                parent::processDefault($field);
        }
    }

    function processModelPHP(BuildPHPClass $PHP) {
        parent::processModelPHP($PHP);
        $PHP->setExtend("CPath\\Model\\DB\\PDOUserModel");

        if(!$this->SessionClass) {
            foreach(self::$mSessionTables as $STable)
                if($STable->Namespace == $this->Namespace) {
                    $this->SessionClass = get_class($STable);
                    break;
                }
            if(!$this->SessionClass)
                $this->SessionClass = "CPath\\Model\\SimpleUserSession";
        }
        $class = $this->SessionClass;
        $Session = new $class;
        if(!($Session instanceof IUserSession))
            throw new BuildException($class . " is not an instance of IUserSession");
        $PHP->addConst('SessionClass', $class);

        if(!$this->FieldID && $this->Primary) $this->FieldID = $this->Primary;
        foreach($this->getColumns() as $Column) {
            if(!$this->FieldUsername && stripos($Column->Name, 'name') !== false)
                $this->FieldUsername = $Column->Name;
            if(!$this->FieldEmail && stripos($Column->Name, 'mail') !== false)
                $this->FieldEmail = $Column->Name;
            if(!$this->FieldPassword && stripos($Column->Name, 'pass') !== false)
                $this->FieldPassword = $Column->Name;
            if(!$this->FieldFlags && stripos($Column->Name, 'flag') !== false)
                $this->FieldFlags = $Column->Name;
        }

        foreach(array('FieldID', 'FieldUsername', 'FieldEmail', 'FieldPassword', 'FieldFlags') as $field) {
            if(!$this->$field)
                throw new BuildException("The field name for {$field} could not be determined");
            $PHP->addConst($field, $this->$field);
        }
    }

    public static function addUserSessionTable(BuildPDOUserSessionTable $Table) {
        self::$mSessionTables[] = $Table;
    }
}

class BuildPDOUserSessionTable extends BuildPDOTable {
    public $FieldKey, $FieldUserID, $FieldExpire;
    public $SessionExpireDays, $SessionExpireSeconds, $SessionKey, $SessionKeyLength;

    public function __construct($name, $comment) {
        parent::__construct($name, $comment);
        BuildPDOUserTable::addUserSessionTable($this);
    }

    function defaultCommentArg($field) {
        list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
        switch(strtolower($name)) {
            case 'fk':
            case 'fieldkey':
                $this->FieldKey = $this->req($name, $arg);
                break;
            case 'fui':
            case 'fielduserid':
                $this->FieldUserID = $this->req($name, $arg);
                break;
            case 'fe':
            case 'fieldexpire':
                $this->FieldExpire = $this->req($name, $arg);
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

    function processModelPHP(BuildPHPClass $PHP) {
        parent::processModelPHP($PHP);
        $PHP->setExtend("CPath\\Model\\DB\\PDOUserSessionModel");

        if(!$this->FieldKey && $this->Primary) $this->FieldKey = $this->Primary;
        foreach($this->getColumns() as $Column) {
            if(!$this->FieldUserID && preg_match('/user.*id/i', $Column->Name))
                $this->FieldUserID = $Column->Name;
            if(!$this->FieldExpire && stripos($Column->Name, 'expire') !== false)
                $this->FieldExpire = $Column->Name;
        }

        foreach(array('FieldKey', 'FieldUserID', 'FieldExpire') as $field) {
            if(!$this->$field)
                throw new BuildException("The field name for {$field} could not be determined");
            $PHP->addConst($field, $this->$field);
        }
    }
}