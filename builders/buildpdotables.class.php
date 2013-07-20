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

    const TMPL_GETPROP = <<<'PHP'

	function get%s() { return $this->mRow['%s']; }

PHP;

    const TMPL_SETPROP = <<<'PHP'
	function set%s($value, $commit=true) { return $this->setField('%s', $value, $commit); }

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

    protected abstract function getTables(\PDO $DB);
    protected abstract function getColumns(\PDO $DB, $table, &$primaryCol, &$primaryAutoInc);
    protected abstract function getIndexes(\PDO $DB, $table, &$primaryCol, &$primaryAutoInc);


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

        $BUILD = $Class->getConstant('BUILD');
        if(!in_array($BUILD, array('ALL', 'MODEL', 'PROC'))) {
            Log::v(__CLASS__, "(BUILD = {$BUILD}) Skipping Build for ".$Class->getName());
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

        foreach($tables as $table) {
            $ucTable = str_replace(' ', '_', ucwords(str_replace('_', ' ', $table)));
            $primaryCol = null;
            $primaryAutoInc = false;
            $indexCols = $this->getIndexes($DB, $table, $primaryCol, $primaryAutoInc);
            $cols = $this->getColumns($DB, $table, $primaryCol, $primaryAutoInc);
            $types = implode('', array_values($cols));
            $searchTypes = '';
            $cols = array_keys($cols);
            $order = array();
            foreach($cols as $i=>$name) {
                if(in_array($name, $indexCols)) {
                    $order[] = $name;
                    $searchTypes .= $types[$i];
                }
            }
            $indexCols = $order;
            $file = strtolower($table).'.class.php';

            // Static Table

            $php = $this->getConst('TableName', $table);
            $php .= $this->getConst('Primary', $primaryCol);
            foreach($cols as $name)
                $php .= $this->getConst(strtoupper($name), $name);
            //$php .= $this->getInsert($table, $cols);
            $php = sprintf(static::TMPL_TABLE_CLASS, $tableNS, $ucTable, $php);
            file_put_contents($tablePath.$file, $php);
            $i = array_search($file, $oldFiles);
            unset($oldFiles[$i]);

            // Model

            $php = $this->getConst('TableName', $table);
            $php .= $this->getConst('Primary', $primaryCol);
            $php .= $this->getConst('Columns', implode(',', $cols));
            $php .= $this->getConst('Types', $types);
            if($indexCols) {
                $php .= $this->getConst('SearchKeys', implode(',', $indexCols));
                $php .= $this->getConst('SearchTypes', $searchTypes);
            }
            foreach($cols as $name)
                $php .= $this->getConst(strtoupper($name), $name);
            foreach($cols as $name)
                $php .= $this->propGetSet($name, $name == $primaryCol);
            //$php .= $this->getCreate($primaryAutoInc ? array_diff($cols, (array)$primaryCol) : $cols);
            //if($indexCols)
            //    $php .= $this->getSearch($primaryAutoInc ? array_diff($indexCols, (array)$primaryCol) : $indexCols);
            $php .= $this->getDelete($ucTable);
            $php = sprintf(static::TMPL_MODEL_CLASS, $modelNS, $Class->getName(), $ucTable, $php);
            file_put_contents($modelPath.$file, $php);

        }
        Log::v(__CLASS__, "Built (".sizeof($tables).") table definition class(es)");
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

    private function propGetSet($name, $justGet=false) {
        $ucName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $php = sprintf(self::TMPL_GETPROP, $ucName, strtolower($name));
        if(!$justGet)
            $php .= sprintf(self::TMPL_SETPROP, $ucName, strtolower($name));
        return $php;
    }

    private function getCreate(Array $cols) {
        return sprintf(self::TMPL_CREATE,
            !$cols ? '' : '$'.implode('=null, $', $cols).'=null');
    }

    private function getSearch(Array $indexs) {
        return sprintf(self::TMPL_SEARCH,
            !$indexs ? '' : '$'.implode('=null, $', $indexs).'=null');
    }

    private function getDelete($name) {
        $ucName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        return sprintf(self::TMPL_DELETE, $name, $ucName, $ucName);
    }
}
