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

    /**
     * @param \PDO $DB
     * @return BuildPDOTable[]
     */
    protected function getTables(\PDO $DB){
        $tables = array();
        foreach($DB->query("SELECT table_name, table_comment FROM information_schema.tables WHERE table_schema='public'") as $row)
            $tables[] = new BuildPDOTable($row['table_name'], $row['table_comment']);
        return $tables;
    }

    /**
     * @param \PDO $DB
     * @param $table
     * @param BuildPDOColumn[] $cols
     * @return void
     */
    protected function getIndexes(\PDO $DB, $table, Array &$cols) {
        foreach($DB->query("select a.attname as column_name
from pg_class t, pg_class i, pg_index ix, pg_attribute a
where t.oid = ix.indrelid and i.oid = ix.indexrelid and a.attrelid = t.oid and a.attnum = ANY(ix.indkey) and t.relkind = 'r' and t.relname = '{$table}'
group by column_name;") as $row ) {
            $name = $row['column_name'];
            $cols[$name]->Index = true;
        }
    }

    /**
     * @param \PDO $DB
     * @param $table
     * @return BuildPDOColumn[]
     */
    protected function getColumns(\PDO $DB, $table) { //, &$primaryCol, &$primaryAutoInc) {
        $primaryCol = NULL;
        $cols = array();
        foreach($DB->query("SELECT * FROM information_schema.columns AS c WHERE c.table_name = '$table';") as $row) {
            $name = $row['column_name'];
            $type = stripos($row['data_type'], 'int') !== false ? 'i' : 's';
            $cols[$name] = new BuildPDOColumn($name, $type, $row['column_comment'], stripos($row['column_default'], 'nextval(') ===0);
            if($cols[$name]->AutoInc && !$primaryCol) {
                $cols[$name]->Primary = true;
                $primaryCol = $name;
            }
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
