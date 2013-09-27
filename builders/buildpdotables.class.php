<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IUserSession;
use CPath\Model\DB\PDOColumn;
use CPath\Model\DB\PDODatabase;
use CPath\Exceptions\BuildException;
use CPath\Base;
use CPath\Build;
use CPath\Interfaces\IBuilder;
use CPath\Log;
use CPath\Builders\Tools\BuildPHPClass;
use CPath\Model\DB\PDOModel;
use CPath\Validate;


class UpgradeException extends \Exception {}

abstract class BuildPDOTables implements IBuilder{

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
        $curVersion = $DB::VERSION;
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
     * @param IBuildable $Buildable
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\Exceptions\BuildException when a build exception occurred
     */
    public function build(IBuildable $Buildable) {

        if(!$Buildable instanceof PDODatabase)
            return false;
        $DB = $Buildable;

        $BUILD = $Buildable::BUILD_DB;
        if(!in_array($BUILD, array('ALL', 'MODEL', 'PROC'))) {
            Log::v(__CLASS__, "(BUILD_DB = {$BUILD}) Skipping Build for ".get_class($Buildable));
            return false;
        }

        $Class = new \ReflectionClass($DB);

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
            $Table->Namespace = $modelNS;

            $this->getColumns($DB, $Table);
            $this->getIndexes($DB, $Table);

            $Table->processArgs();

            foreach($Table->getColumns() as $Column) {
                if($Column->Flags & PDOColumn::FLAG_INDEX)
                    $Column->Flags |= PDOColumn::FLAG_SEARCH;
                if(!($Column->Flags & PDOColumn::FLAG_NULL) && !($Column->Flags & PDOColumn::FLAG_AUTOINC) && !($Column->Flags & PDOColumn::FLAG_DEFAULT))
                    $Column->Flags |= PDOColumn::FLAG_REQUIRED;
            }
        }

        // Model

        foreach($tables as $Table) {
            $file = $modelPath.strtolower($Table->ClassName).'.class.php';

            $PHP = new BuildPHPClass($Table->ClassName);
            $PHP->Namespace = $modelNS;

            $Table->processModelPHP($PHP);

            $PHP->addUse(get_class($DB), 'DB');

            $PHP->addConst('TABLE', $Table->Name);
            $PHP->addConst('MODEL_NAME', $Table->ModelName);

            $PHP->addConst('PRIMARY', $Table->Primary);

            $columns = "\n\t\tstatic \$columns = NULL;";
            $columns .= "\n\t\treturn \$columns ?: \$columns = array(";
            $i=0;
            foreach($Table->getColumns() as $Column) {
                if($i++) $columns .= ',';
                $columns .= "\n\t\t\t" . var_export($Column->Name, true) . ' => new PDOColumn(';
                $columns .= var_export($Column->Name, true);
                $columns .= ',0x' . dechex($Column->Flags ?: 0);
                if($Column->Comment || $Column->Filter || $Column->EnumValues)
                    $columns .= ',' . ($Column->Filter ?: 0);
                if($Column->Comment || $Column->EnumValues)
                    $columns .= ',' . var_export($Column->Comment ?: '', true);
                if($Column->EnumValues) {
                    $a = '';
                    foreach($Column->EnumValues as $e)
                        $a .= ($a ? ',' : '') . var_export($e, true);
                    $columns .= ',array('.$a.')';
                }
                $columns .= ")";
            }
            $columns .= "\n\t\t);\n";

            //$PHP->addStaticMethod('init', NULL, $columns, 'public', false);
            //$PHP->addStaticMethod('getColumns', NULL, ' return self::$_columns; ', 'protected', false);
            $PHP->addStaticMethod('loadAllColumns', NULL, $columns, '', false);
            //$PHP->addStaticProperty('_columns', NULL, 'private');
            $PHP->addUse('CPath\Model\DB\PDOColumn');

//            $InsertFields = array();
//            $UpdateFields = array();
//            $SearchFields = array();
//            $ExportFields = array();
//
//            foreach($Table->getColumns() as $Column) {
//                if($Column->Flags & PDOColumn::FLAG_INSERT) $InsertFields[] = $Column->Name;
//                if($Column->Flags & PDOColumn::FLAG_UPDATE) $UpdateFields[] = $Column->Name;
//                if($Column->Flags & PDOColumn::FLAG_SEARCH) $SearchFields[] = $Column->Name;
//                if($Column->Flags & PDOColumn::FLAG_EXPORT) $ExportFields[] = $Column->Name;
//            }
//
//            if(!$InsertFields && $Table->INSERT) $InsertFields[] = $Table->INSERT;
//            if(!$UpdateFields && $Table->UPDATE) $UpdateFields[] = $Table->UPDATE;
//            if(!$SearchFields && $Table->SEARCH) $SearchFields[] = $Table->SEARCH;
//            if(!$ExportFields && $Table->EXPORT) $ExportFields[] = $Table->EXPORT;

//            if($InsertFields) $PHP->addConst('INSERT', implode(',', $InsertFields));
//            if($UpdateFields) $PHP->addConst('UPDATE', implode(',', $UpdateFields));
//            if($SearchFields) $PHP->addConst('SEARCH', implode(',', $SearchFields));
//            if($ExportFields) $PHP->addConst('EXPORT', implode(',', $ExportFields));

            if($Table->SearchWildCard)
                $PHP->addConst('SEARCH_WILDCARD', true);
            if($Table->SearchLimit)
                $PHP->addConst('SEARCH_LIMIT', $Table->SearchLimit);
            if($Table->SearchLimitMax)
                $PHP->addConst('SEARCH_LIMIT_MAX', $Table->SearchLimitMax);
            if($Table->AllowHandler)
                $PHP->addImplements('CPath\Interfaces\IBuildable');
                //$PHP->addConst('BUILD_IGNORE', false);

            $PHP->addConstCode();
            $PHP->addConstCode("// Table Columns ");
            foreach($Table->getColumns() as $Column)
                $PHP->addConst($this->toTitleCase($Column->Name, true), $Column->Name);

            foreach($Table->getColumns() as $Column)
                $PHP->addProperty($Column->Name);

            foreach($Table->getColumns() as $Column) {
                $ucName = self::toTitleCase($Column->Name, true);
                $PHP->addMethod('get' . $ucName, '', sprintf(' return $this->%s; ', strtolower($Column->Name)));
                if($Column->Flags & PDOColumn::FLAG_PRIMARY ? false : true)
                    $PHP->addMethod('set' . $ucName, '$value, $commit=true', sprintf(' return $this->updateColumn(\'%s\', $value, $commit); ', strtolower($Column->Name)));
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

        $PHP = new BuildPHPClass('Procs');
        $PHP->Namespace = $procNS;
        $PHP->addUse(get_class($DB), 'DB');
        $names = array();
        foreach($procs as $proc) {
            $name = array_shift($proc);
            if(isset($names[$name])) {
                $name .= ++$names[$name];
            } else {
                $names[$name] = 1;
            }
            $method = $name.'('.(!$proc ? '' : ('%s'.str_repeat(', %s', sizeof($proc)-1))).')';
            $PHP->addConst(strtoupper($name), $method);

            $sqlParams = $proc ? '?'.str_repeat(', ?', sizeof($proc)-1) : '';
            $codeParams = $proc ? '$'.implode(', $', $proc) : '';

            $code = <<<PHP
        static \$stmd = NULL;
        if(!\$stmd) \$stmd = self::getDB()->prepare('SELECT $name({$sqlParams})');
        \$stmd->execute(array({$codeParams}));
        return \$stmd;
PHP;

            $ucName = self::toTitleCase($name, true);
            $ucName[0] = strtolower($ucName[0]);
            $PHP->addStaticMethod($ucName, $proc, $code);
            //$phpC .= self::getConst(strtoupper($name), $method);
        }

        $PHP->addStaticMethod('getDB', '', " return DB::get(); ");
        //$php = sprintf(self::TMPL_PROC_CLASS, $procNS, $phpC.$phpP);
        //file_put_contents($procPath.'procs.class.php', $php);
        file_put_contents($procPath.'procs.class.php', $PHP->build());
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


    static function toTitleCase($field, $noSpace=false) {
        $field = ucwords(str_replace('_', ' ', $field));
        $words = explode(' ', $field);
        foreach($words as &$word) {
            if(strlen($word) === 2)
                $word = strtoupper($word);
        }
        if(!$noSpace) return implode(' ', $words);;
        return implode('', $words);
    }

    static function createBuildableInstance() {
        return new static;
    }
}

class BuildPDOColumn {
    public $Name, $Comment, $Flags=0, $EnumValues, $Filter=NULL;

    public function __construct($name, $comment) {
        $this->Name = $name;
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace') , $comment);
        if(!$this->Comment)
            $this->Comment = $comment;
        if($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
    }

    function replace(array $matches) {
        foreach(explode('|', $matches[1]) as $field) {
            $args = explode(':', $field, 2);
            switch(strtolower($args[0])) {
                case 'i':
                case 'insert':
                    $this->Flags |= PDOColumn::FLAG_INSERT;
                    break;
                case 'u':
                case 'update':
                    $this->Flags |= PDOColumn::FLAG_UPDATE;
                    break;
                case 's':
                case 'search':
                    $this->Flags |= PDOColumn::FLAG_SEARCH;
                    break;
                case 'e':
                case 'export':
                    $this->Flags |= PDOColumn::FLAG_EXPORT;
                    break;
                case 'r':
                case 'required':
                    $this->Flags |= PDOColumn::FLAG_REQUIRED;
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

    public $Name, $Title, $ClassName, $Namespace, $ModelName, $Comment,
        $SearchWildCard, $SearchLimit, $SearchLimitMax, $AllowHandler = false, $Primary, $Template;
    protected $mColumns = array();
    protected $mUnfound = array();
    protected $mArgs = array();

    protected function __construct($name, $comment) {
        $this->Name = $name;
        $this->Title = ucwords(str_replace('_', ' ', $this->Name));
        $this->ModelName = $this->Title;
        $this->ClassName = str_replace(' ', '', $this->Title) . 'Model';
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace'), $comment);
        if(!$this->Comment)
            $this->Comment = $comment;
        if($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
    }

    function processArgs() {
        foreach($this->mArgs as $field) {
            list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
            switch(strtolower($name)) {
                case 'i':
                case 'insert':
                    foreach(explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_INSERT;
                    break;
                case 'u':
                case 'update':
                    foreach(explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_UPDATE;
                    break;
                case 's':
                case 'search':
                    foreach(explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_SEARCH;
                    break;
                case 'e':
                case 'export':
                    foreach(explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_EXPORT;
                    break;
                case 'r':
                case 'required':
                    foreach(explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_REQUIRED;
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
        $this->mArgs = array();
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
                default:
                    $this->mArgs[] = $field;
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
     * @throws BuildException if the column is not found
     */
    public function getColumn($name) {
        if(!isset($this->mColumns[$name]))
            throw new BuildException("Column '{$name}' not found". print_r($this, true));
        return $this->mColumns[$name];
    }

    public function addColumn(BuildPDOColumn $Column) {
        $this->mColumns[$Column->Name] = $Column;
    }

    function processModelPHP(BuildPHPClass $PHP) {
        $this->processArgs();
        $PHP->setExtend("CPath\\Model\\DB\\PDOModel");

        if($this->mUnfound)
            throw new BuildException("Invalid Table Comment Token '" . implode('|', $this->mUnfound) . "' in Table '{$this->Name}'");

        if(!$this->Primary)
            foreach($this->getColumns() as $Column) {
                if($Column->Flags & PDOColumn::FLAG_PRIMARY) {
                    $this->Primary = $Column->Name;
                    break;
                }
            }

        if(!$this->Primary)
            Log::e(__CLASS__, "Warning: No PRIMARY key found for Table '{$this->Name}'");
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
    public $Column_ID, $Column_Username, $Column_Email, $Column_Password, $Column_Flags, $Session_Class;
    /** @var BuildPDOUserSessionTable[] */
    protected static $mSessionTables = array();

    function defaultCommentArg($field) {
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
            case 'cc':
            case 'sessionclass':
                $this->Session_Class = $this->req($name, $arg);
                break;
            default:
                parent::processDefault($field);
        }
    }

    function processModelPHP(BuildPHPClass $PHP) {
        parent::processModelPHP($PHP);
        $PHP->setExtend("CPath\\Model\\DB\\PDOUserModel");

        if(!$this->Session_Class) {
            foreach(self::$mSessionTables as $STable)
                if($STable->Namespace == $this->Namespace) {
                    $this->Session_Class = $STable->Namespace . '\\' . $STable->ClassName;
                    break;
                }
        }
        if(!$this->Session_Class) {
            $this->Session_Class = "CPath\\Model\\SimpleUserSession";
            Log::e(__CLASS__, "Warning: No User session class found for Table '{$this->Name}'. Defaulting to SimpleUserSession");
        }
        $class = $this->Session_Class;
        //$Session = new $class;
        //if(!($Session instanceof IUserSession))
        //    throw new BuildException($class . " is not an instance of IUserSession");
        $PHP->addConst('SESSION_CLASS', $class);

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
            $PHP->addConst(strtoupper($field), $this->$field);
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
        $Column->Flags |= PDOColumn::FLAG_REQUIRED | PDOColumn::FLAG_INSERT;
        if(!$Column->Filter)
            $Column->Filter = Validate::FILTER_VALIDATE_PASSWORD;
    }

    public static function addUserSessionTable(BuildPDOUserSessionTable $Table) {
        self::$mSessionTables[] = $Table;
    }
}

class BuildPDOUserSessionTable extends BuildPDOTable {
    public $Column_Key, $Column_User_ID, $Column_Expire;
    public $SessionExpireDays, $SessionExpireSeconds, $SessionKey, $SessionKeyLength;

    public function __construct($name, $comment) {
        parent::__construct($name, $comment);
        BuildPDOUserTable::addUserSessionTable($this);
    }

    function defaultCommentArg($field) {
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

    function processModelPHP(BuildPHPClass $PHP) {
        parent::processModelPHP($PHP);
        $PHP->setExtend("CPath\\Model\\DB\\PDOUserSessionModel");

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
            $PHP->addConst(strtoupper($field), $this->$field);
        }
    }
}