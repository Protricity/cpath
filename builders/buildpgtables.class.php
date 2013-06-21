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
use CPath\Log;


class UpgradeException extends \Exception {}
/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class BuildPGTables {
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

    const TMPL_INSERT =
"\tstatic function insert(\\PDO \$DB%s) {
\t\tstatic \$stmd = NULL;
\t\tif(!\$stmd) \$stmd = \$DB->prepare('INSERT INTO %s VALUES (%s)%s');
\t\t\$stmd->execute(array(%s));
%s\t}\n";

    const TMPL_PROC =
"\n\tstatic function %s(\\PDO \$DB%s) {
\t\tstatic \$stmd = NULL;
\t\tif(!\$stmd) \$stmd = \$DB->prepare('SELECT %s(%s)');
\t\t\$stmd->execute(array(%s));
\t\treturn \$stmd;
\t}\n";

    public static function upgrade(PostGreSQL $DB) {
        $oldVersion = $DB->getDBVersion();
        $curVersion = $DB::VERSION;
        $Class = new \ReflectionClass($DB);
        $schemaFolder = self::getFolder($Class, 'schema');
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
        Log::v(__CLASS__, "Upgraded Database from version $curVersion to $oldVersion");
    }

    /**
     * Builds class references for existing database tables
     * @param \ReflectionClass $Class
     */
    public static function build(\ReflectionClass $Class) {

        if(!$Class->isSubclassOf(self::PostGreSQL))
            throw new BuildException("Class ".$Class->getName()." does not implement ".self::PostGreSQL);

        /* @var $DB PostGreSQL */
        $DB = call_user_func(array($Class->getName(), 'get'));

        $tablePath = self::getFolder($Class, 'tables');
        $tableNS = $Class->getNamespaceName() . "\\Tables";
        $procPath = self::getFolder($Class, 'procs');
        $procNS = $Class->getNamespaceName() . "\\Procs";

        $Config =& Build::getConfig($Class->getName());
        $schemaFolder = self::getFolder($Class, 'schema');
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
            return;
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

            $php = self::getConst('TableName', $table);
            foreach($cols as $name=>$type)
                $php .= self::getConst(strtoupper($name), $name);
            //$php .= self::getInsert($table, $cols);
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
        foreach($procs as $proc) {
            $name = array_shift($proc);
            $method = $name.'('.(!$proc ? '' : ('%s'.str_repeat(', %s', sizeof($proc)-1))).')';
            $phpC .= self::getConst(strtoupper($name), $method);
            $phpP .= self::getProc($name, $proc);
        }
        $php = sprintf(self::TMPL_PROC_CLASS, $procNS, $phpC.$phpP);
        file_put_contents($procPath.'procs.class.php', $php);
        Log::v(__CLASS__, "Built (".sizeof($tables).") routines");
    }
    /**
     * Checks to see if any routes were built, and builds them into [gen]/routes.php
     * @param \ReflectionClass $Class
     */
    public static function buildComplete(\ReflectionClass $Class) {
    }

    private static function getFolder(\ReflectionClass $Class, $subFolder=NULL) {
        if($subFolder) $subFolder .= '/';
        return dirname($Class->getFileName()) . '/' . $subFolder;
    }

    private static function getConst($name, $value) {
        return "\tConst {$name} = '{$value}';\n";
    }

    private static function getInsert($table, $columns) {
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

    private static function getProc($name, $params) {
        return sprintf(self::TMPL_PROC,
            $name, !$params ? '' : ', $'.implode(', $', $params),
            $name, !$params ? '' : '?'.str_repeat(', ?', sizeof($params)-1),
            '$'.implode(', $', $params));
    }
}
