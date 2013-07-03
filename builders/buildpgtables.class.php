<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\DataBase\PostGreSQL;
use CPath\BuildException;
use CPath\Base;
use CPath\Build;
use CPath\Interfaces\IBuilder;
use CPath\Log;


class UpgradeException extends \Exception {}
/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class BuildPGTables implements IBuilder{
    const PostGreSQL = "CPath\\DataBase\\PostGreSQL";

    const TMPL_TABLE_CLASS = "<?php
namespace %s;
use CPath\\Database\\PDOTable;
class %s extends PDOTable {
%s}";

    const TMPL_PROC_CLASS = "<?php
namespace %s;
class Procs {
%s}";

    const TMPL_INSERT = <<<'PHP'
    static function insert(\PDO $DB%s) {
        static $stmd = NULL;
        if(!$stmd) $stmd = $DB->prepare('INSERT INTO %s VALUES (%s)%s');
        $stmd->execute(array(%s));
%s  }
PHP;

    const TMPL_PROC =
"\n\tstatic function %s(\\PDO \$DB%s) {
\t\tstatic \$stmd = NULL;
\t\tif(!\$stmd) \$stmd = \$DB->prepare('SELECT %s(%s)');
\t\t\$stmd->execute(array(%s));
\t\treturn \$stmd;
\t}\n";

    public function upgrade(PostGreSQL $DB, $oldVersion=NULL) {
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
        Log::v(__CLASS__, "Upgraded Database from version $oldVersion to $curVersion");
    }

    /**
     * Builds class references for existing database tables
     * @param \ReflectionClass $Class
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\BuildException when a build exception occurred
     */
    public function build(\ReflectionClass $Class) {

        if(!$Class->isSubclassOf(self::PostGreSQL))
            return false;

        /* @var $DB PostGreSQL */
        $DB = call_user_func(array($Class->getName(), 'get'));

        $tablePath = $this->getFolder($Class, 'tables');
        $tableNS = $Class->getNamespaceName() . "\\Tables";
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

        foreach(scandir($schemaFolder) as $file)
            $hash += filemtime($schemaFolder.$file);
        if(!$force && isset($Config['schemaHash']) && $Config['schemaHash'] == $hash) {
            Log::v(__CLASS__, "Skipping Build for ".$Class->getName());
            return false;
        }
        $Config['schemaHash'] = $hash;


        // Tables

        $tables = array();
        foreach($DB->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'") as $row)
            $tables[] = $row['table_name'];

        foreach($tables as $table) {
            $ucTable = str_replace(' ', '_', ucwords(str_replace('_', ' ', $table)));
            $cols = array();
            foreach($DB->query("SELECT * FROM information_schema.columns WHERE table_name = '$table';") as $row) {
                $name = $row['column_name'];
                $cols[$name] = '?';
                if(stripos($row['column_default'], 'nextval(') ===0)
                    $cols[$name] = 'DEFAULT';
            }

            $php = $this->getConst('TableName', $table);
            foreach($cols as $name=>$type)
                $php .= $this->getConst(strtoupper($name), $name);
            //$php .= $this->getInsert($table, $cols);
            $php = sprintf(self::TMPL_TABLE_CLASS, $tableNS, $ucTable, $php);
            $file = strtolower($table).'.class.php';
            file_put_contents($tablePath.$file, $php);
            $i = array_search($file, $oldFiles);
            unset($oldFiles[$i]);
        }
        Log::v(__CLASS__, "Built (".sizeof($tables).") table classes");
        if($c = sizeof($oldFiles)) {
            Log::v(__CLASS__, "Removing ({$c}) depreciated table classes");
            foreach($oldFiles as $file) unlink($tablePath.$file);
        }

        // Stored Procedures

        $procs = array();
        foreach($DB->query(
            "SELECT r.routine_name, r.specific_name, p.parameter_name FROM information_schema.routines r"
            ."  LEFT JOIN information_schema.parameters p on r.specific_name = p.specific_name"
            ."  WHERE routine_schema = 'public' AND p.parameter_mode = 'IN'"
            ."  ORDER BY r.specific_name, p.ordinal_position") as $row) {
            $name = $row['routine_name'];
            $sname = $row['specific_name'];
            if(empty($procs[$sname])) $procs[$sname] = array($name);
            if($row['parameter_name'])
                $procs[$sname][] = $row['parameter_name'];
        }

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
        Log::v(__CLASS__, "Built (".sizeof($tables).") routines");

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
        return "\tConst {$name} = '{$value}';\n";
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
}
