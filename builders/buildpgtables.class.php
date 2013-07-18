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


class BuildPGTables extends BuildPDOTables {
    const DB_CLASSNAME = "CPath\\Model\\DB\\PGSQLDatabase";

    const TMPL_TABLE_CLASS = <<<PHP
<?php
namespace %s;
use CPath\Model\DB\PGSQLTable;
class %s extends PGSQLTable {
%s}
PHP;

    protected function getTables(\PDO $DB){
        $tables = array();
        foreach($DB->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'") as $row)
            $tables[] = $row['table_name'];
        return $tables;
    }

    protected function getColumns(\PDO $DB, $table, &$primaryCol, &$indexCols) {
        $cols = array();
        foreach($DB->query("SELECT * FROM information_schema.columns WHERE table_name = '$table';") as $row) {
            $name = $row['column_name'];
            $cols[$name] = '?';
            if(stripos($row['column_default'], 'nextval(') ===0)
                $cols[$name] = 'DEFAULT';
        }
        return $cols;
    }

    protected function getProcs(\PDO $DB) {
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
        return $procs;
    }
}
