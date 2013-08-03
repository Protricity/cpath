<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\Model\DB\PDODatabase;
use CPath\BuildException;
use CPath\Base;
use CPath\Build;
use CPath\Interfaces\IBuilder;
use CPath\Log;


class UpgradeException extends \Exception {}

abstract class BuildPDOTables implements IBuilder{
    const DB_CLASSNAME = "CPath\\Model\\DB\\PDODatabase";

    const TMPL_TABLE_CLASS = null;

    const TMPL_MODEL_CLASS = <<<'PHP'
<?php
namespace %s;
use %s as DB;
use CPath\Model\DB\PDOModel;
class %s extends PDOModel{
%s
    static function getDB() { return DB::get(); }
}
PHP;

    const TMPL_PROC_CLASS = <<<PHP
<?php
namespace %s;
class Procs {
%s}
PHP;

    const TMPL_INSERT = <<<'PHP'
	static function insert(\PDO $DB%s) {
		static $stmd = NULL;
		if(!$stmd) $stmd = $DB->prepare('INSERT INTO %s VALUES (%s)%s');
		$stmd->execute(array(%s));
%s  }
PHP;

    const TMPL_PROC = <<<'PHP'
	static function %s(\PDO $DB%s) {
		static $stmd = NULL;
		if(!$stmd) $stmd = $DB->prepare('SELECT %s(%s)');
		$stmd->execute(array(%s));
		return $stmd;
	}
PHP;

    const TMPL_GETENUM = <<<'PHP'

	static function get%sEnumValues() { return %s; }

PHP;

    const TMPL_PROP = <<<'PHP'
	protected $%s;

PHP;

    const TMPL_GETPROP = <<<'PHP'

	function get%s() { return $this->%s; }

PHP;

    const TMPL_SETPROP = <<<'PHP'
	function set%s($value, $commit=true) { return $this->setField('%s', $value, $commit%s); }

PHP;

    const TMPL_CREATE = <<<'PHP'

	static function create(%s) { return parent::createA(get_defined_vars()); }

PHP;

    const TMPL_SEARCH = <<<'PHP'

	static function search(%s) { return parent::searchA(get_defined_vars()); }

PHP;

    const TMPL_DELETE = <<<'PHP'

	static function remove(%s $%s) { parent::removeModel($%s); }

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
     * @param $table
     * @return BuildPDOColumn[]
     */
    protected abstract function getColumns(\PDO $DB, $table); // , &$primaryCol, &$primaryAutoInc

    /**
     * @param \PDO $DB
     * @param $table
     * @param BuildPDOColumn[] $cols
     * @return void
     */
    protected abstract function getIndexes(\PDO $DB, $table, Array &$cols); //, &$primaryCol, &$primaryAutoInc);


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

        $tablePath = $this->getFolder($Class, 'tables');
        $tableNS = $Class->getNamespaceName() . "\\Tables";
        $modelPath = $this->getFolder($Class, 'model');
        $modelNS = $Class->getNamespaceName() . "\\Model";
        $procPath = $this->getFolder($Class, 'procs');
        $procNS = $Class->getNamespaceName() . "\\Procs";

        $Config =& Build::getConfig($Class->getName());
        $schemaFolder = $this->getFolder($Class, 'schema');
        $hash = 0;
        $force = Build::force();

        $oldFiles = array();
        if(!file_exists($tablePath)) { mkdir($tablePath, null, true); $force = true; }
        else $oldFiles = array_diff(scandir($tablePath), array('..', '.'));
        if(!file_exists($procPath))  { mkdir($procPath, null, true);  $force = true; }
        if(!file_exists($modelPath)) { mkdir($modelPath, null, true); $force = true; }

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
            $shortTable = str_replace(' ', '', ucwords(str_replace('_', ' ', $Table->Name)));

            /** @var BuildPDOColumn[] $cols  */
            $cols = $this->getColumns($DB, $Table->Name);
            $this->getIndexes($DB, $Table->Name, $cols);

            $file = strtolower($shortTable).'.class.php';

            // Model

            $php = $this->getConst('TableName', $Table->Name);
            $primaryCol = NULL;
            foreach($cols as $Column) {
                if($Column->Primary) {
                    $primaryCol = $Column->Name;
                    break;
                }
            }
            $php .= $this->getConst('Primary', $primaryCol);

            $colNames = array();
            foreach($cols as $Column)
                $colNames[] = $Column->Name;
            $php .= $this->getConst('Columns', implode(',', $colNames));

            $comments = array();
            foreach($cols as $Column)
                $comments[] = $Column->Comment;
            $php .= $this->getConst('Comments', implode(';', $comments));

            $types = '';
            $InsertFields = array();
            $UpdateFields = array();
            $SearchFields = array();
            $ExportFields = array();
            //$FilterFields = array();
            //$filterFound = false;
            foreach($cols as $Column) {
                if($Column->Insert) $InsertFields[] = $Column->Name;
                if($Column->Update) $UpdateFields[] = $Column->Name;
                if($Column->Search) $SearchFields[] = $Column->Name;
                if($Column->Export) $ExportFields[] = $Column->Name;
                //$FilterFields[] = $Column->Filter;
                //if($Column->Filter) $filterFound = true;
                $types .= $Column->Type;
            }
            if(!$InsertFields && $Table->Insert) $InsertFields[] = $Table->Insert;
            if(!$UpdateFields && $Table->Update) $UpdateFields[] = $Table->Update;
            if(!$SearchFields && $Table->Search) $SearchFields[] = $Table->Search;
            if(!$ExportFields && $Table->Export) $ExportFields[] = $Table->Export;

            if(!$SearchFields)
                foreach($cols as $Column)
                    if($Column->Index)
                        $SearchFields[] = $Column->Name;

            $php .= $this->getConst('Types', $types);

            if($InsertFields) $php .= $this->getConst('Insert', implode(',', $InsertFields));
            if($UpdateFields) $php .= $this->getConst('Update', implode(',', $UpdateFields));
            if($SearchFields) $php .= $this->getConst('Search', implode(',', $SearchFields));
            if($ExportFields) $php .= $this->getConst('Export', implode(',', $ExportFields));
            //if($filterFound)  $php .= $this->getConst('Filter', implode(',', $FilterFields));

            $php .= "\n";
            foreach($cols as $Column)
                $php .= $this->getConst(strtoupper($Column->Name), $Column->Name);
            $php .= "\n";
            foreach($cols as $Column)
                $php .= sprintf(self::TMPL_PROP, $Column->Name);
            foreach($cols as $Column)
                $php .= $this->propGetSet($Column->Name, $Column->Primary, $Column->Filter);

            foreach($cols as $Column)
                if($Column->Enum)
                    $php .= $this->enumGet($Column->Name, $Column->Enum);

            $php .= $this->getDelete($shortTable);
            $php = sprintf(static::TMPL_MODEL_CLASS, $modelNS, $Class->getName(), $shortTable, $php);
            file_put_contents($modelPath.$file, $php);

        }
        //Log::v(__CLASS__, "Built (".sizeof($tables).") table definition class(es)");
        Log::v(__CLASS__, "Built (".sizeof($tables).") table model(s)");
        if($c = sizeof($oldFiles)) {
            Log::v(__CLASS__, "Removing ({$c}) depreciated table classes");
            foreach($oldFiles as $file) unlink($tablePath.$file);
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
            $phpC .= $this->getConst(strtoupper($name), $method);
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

    private function getConst($name, $value) {
        return "\tConst {$name} = ".var_export($value, true).";\n";
    }

    private function getInsert($table, $columns) {
        $qs = NULL;
        $retSQL = '';
        $ret = '';
        foreach($columns as $name => $type) {
            if($qs) $qs .= ', ';
            $qs .= $type;
            if(strpos($type, '?') === false) {
                $retSQL = " RETURNING ".$name;
                $ret = "\t\treturn \$stmd->fetchColumn(0);\n";
                unset($columns[$name]);
            }
        }
        $p = '$'.implode(', $', array_keys($columns));
        return sprintf(self::TMPL_INSERT, !$columns ? '' : ', '.$p, $table, $qs, $retSQL, $p, $ret);
    }

    private function getProc($name, $params) {
        return sprintf(self::TMPL_PROC,
            $name, !$params ? '' : ', $'.implode(', $', $params),
            $name, !$params ? '' : '?'.str_repeat(', ?', sizeof($params)-1),
            '$'.implode(', $', $params));
    }

    private function propGetSet($name, $justGet=false, $filter=NULL) {
        $ucName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $php = sprintf(self::TMPL_GETPROP, $ucName, strtolower($name));
        if(!$justGet)
            $php .= sprintf(self::TMPL_SETPROP, $ucName, strtolower($name), ($filter ? ', '.$filter : ''));
        return $php;
    }

    private function enumGet($name, Array $enums) {
        $ucName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $a = '';
        foreach($enums as $e)
            $a .= ($a ? ',' : '') . var_export($e, true);
        return sprintf(self::TMPL_GETENUM, $ucName, 'array('.$a.')');
    }

    private function getCreate(Array $cols) {
        return sprintf(self::TMPL_CREATE,
            !$cols ? '' : '$'.implode('=null, $', $cols).'=null');
    }

    private function getSearch(Array $indexs) {
        return sprintf(self::TMPL_SEARCH,
            !$indexs ? '' : '$'.implode('=null, $', $indexs).'=null');
    }

    private function getDelete($shortTable) {
        return sprintf(self::TMPL_DELETE, $shortTable, $shortTable, $shortTable);
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
        $Column = $this;
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', function(array $matches) use ($Column) {
            foreach(explode('|', $matches[1]) as $field) {
                $args = explode(':', $field, 2);
                switch(strtolower($args[0])) {
                    case 'i':
                    case 'insert':
                        $Column->Insert = true;
                        break;
                    case 'u':
                    case 'update':
                        $Column->Update = true;
                        break;
                    case 's':
                    case 'search':
                        $Column->Search = true;
                        break;
                    case 'e':
                    case 'export':
                        $Column->Export = true;
                        break;
                    case 'c':
                    case 'comment':
                        if(isset($args[1]))
                            $Column->Comment = $args[1];
                        break;
                    case 'f':
                    case 'filter':
                        if(isset($args[1])) {
                            $fArgs = explode(':', $args[1]);
                            if(!is_numeric($fArgs[0])) $fArgs[0] = constant($fArgs[0]);
                            $Column->Filter |= $fArgs[0];
                        }
                        break;
                }
            }
            return '';
        }, $comment);
        if(!$this->Comment)
            $this->Comment = $comment;
        if($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
        $this->AutoInc = $autoInc;
    }
}

class BuildPDOTable {
    public $Name, $Comment, $Insert, $Update, $Search, $Export;
    public function __construct($name, $comment) {
        $this->Name = $name;
        $Table = $this;
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', function(array $matches) use ($Table) {
            foreach(explode('|', $matches[1]) as $field) {
                $args = explode(':', $field, 2);
                switch(strtolower($args[0])) {
                    case 'i':
                    case 'insert':
                        if(isset($args[1])) $Table->Insert = $args[1];
                        break;
                    case 'u':
                    case 'update':
                        if(isset($args[1])) $Table->Update = $args[1];
                        break;
                    case 's':
                    case 'search':
                        if(isset($args[1])) $Table->Search = $args[1];
                        break;
                    case 'e':
                    case 'export':
                        if(isset($args[1])) $Table->Export = $args[1];
                        break;
                    case 'c':
                    case 'comment':
                        if(isset($args[1]))
                            $Table->Comment = $args[1];
                        break;
                }
            }
            return '';
        }, $comment);
        if(!$this->Comment)
            $this->Comment = $comment;
        if($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
    }
}