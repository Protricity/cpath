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
use CPath\Model\DB\PDOModel;


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
        foreach($DB->query("SELECT table_name, obj_description(table_name::regclass) as table_comment
        FROM information_schema.tables t
        LEFT JOIN pg_class c on c.relname = t.table_name
        WHERE table_schema='public'") as $row)
            $tables[] = BuildPDOTable::create($row['table_name'], $row['table_comment']);
        return $tables;
    }

    /**
     * @param \PDO $DB
     * @param BuildPDOTable $Table
     * @return void
     */
    protected function getIndexes(\PDO $DB, BuildPDOTable $Table) {
        foreach($DB->query("select a.attname as column_name
from pg_class t, pg_class i, pg_index ix, pg_attribute a
where t.oid = ix.indrelid and i.oid = ix.indexrelid and a.attrelid = t.oid and a.attnum = ANY(ix.indkey) and t.relkind = 'r' and t.relname = '{$Table->Name}'
group by column_name;") as $row ) {
            $name = $row['column_name'];
            $Column = $Table->getColumn($name);
            $Column->Flags |= PDOModel::ColumnIsIndex;
        }
    }

    /**
     * @param \PDO $DB
     * @param BuildPDOTable $Table
     * @return void
     */
    protected function getColumns(\PDO $DB, BuildPDOTable $Table) {
        $primaryCol = NULL;
        foreach($DB->query("SELECT c.column_name, c.data_type, c.column_default, d.description as column_comment
        FROM information_schema.columns AS c
        LEFT JOIN (
        SELECT c.table_schema,c.table_name,c.column_name,pgd.description
            FROM pg_catalog.pg_statio_all_tables as st
            inner join pg_catalog.pg_description pgd on (pgd.objoid=st.relid)
            inner join information_schema.columns c on (pgd.objsubid=c.ordinal_position
            and  c.table_schema=st.schemaname and c.table_name=st.relname)
        ) d on d.column_name = c.column_name
        WHERE c.table_name = '{$Table->Name}';") as $row) {
            $name = $row['column_name']; // TODO: get NULL $Column->Flags |= PDOModel::ColumnIsNull;
            $Column = new BuildPDOColumn($name, $row['column_comment']);
            if(stripos($row['data_type'], 'int') !== false)
                $Column->Flags |= PDOModel::ColumnIsNumeric;
            if(stripos($row['column_default'], 'nextval(') ===0)
                $Column->Flags |= PDOModel::ColumnIsAutoInc;
            if(($Column->Flags & PDOModel::ColumnIsAutoInc) && !$primaryCol) {
                $Column->Flags |= PDOModel::ColumnIsPrimary;
                $primaryCol = $name;
            }
            $Table->addColumn($Column);
        }
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
