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
use CPath\Interfaces\IBuildable;
use CPath\Model\DB\PDOColumn;
use CPath\Model\DB\PGSQLDatabase;


class BuildPGTables extends BuildPDOTables implements IBuildable {

    const TMPL_TABLE_CLASS = <<<PHP
<?php
namespace %s;
use CPath\Model\DB\PGSQLTable;
class %s extends PGSQLTable {
%s}
PHP;

    /**
     * Builds class references for existing database tables
     * @param IBuildable $Buildable
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\Exceptions\BuildException when a build exception occurred
     */
    public function build(IBuildable $Buildable) {
        if(!$Buildable instanceof PGSQLDatabase)
            return false;
        return parent::build($Buildable);
    }

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
            $tables[] = $this->createTable($row['table_name'], $row['table_comment']);
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
            $Column->Flags |= PDOColumn::FLAG_INDEX;
        }
    }

    /**
     * @param \PDO $DB
     * @param BuildPDOTable $Table
     * @return void
     */
    protected function getColumns(\PDO $DB, BuildPDOTable $Table) {
        $primaryCol = NULL;
        foreach($DB->query("SELECT c.column_name, c.data_type, c.column_default, c.is_nullable, d.description as column_comment
        FROM information_schema.columns AS c
        LEFT JOIN (
        SELECT c.table_schema,c.table_name,c.column_name,pgd.description
            FROM pg_catalog.pg_statio_all_tables as st
            inner join pg_catalog.pg_description pgd on (pgd.objoid=st.relid)
            inner join information_schema.columns c on (pgd.objsubid=c.ordinal_position
            and  c.table_schema=st.schemaname and c.table_name=st.relname)
        ) d on d.column_name = c.column_name
        WHERE c.table_name = '{$Table->Name}';") as $row) {
            $name = $row['column_name'];
            $Column = new BuildPDOColumn($name, $row['column_comment']);
            if(strcasecmp($row['is_nullable'], 'yes') === 0)
                $Column->Flags |= PDOColumn::FLAG_NULL;
            if(stripos($row['data_type'], 'int') !== false)
                $Column->Flags |= PDOColumn::FLAG_NUMERIC;
            if(stripos($row['column_default'], 'nextval(') ===0)
                $Column->Flags |= PDOColumn::FLAG_AUTOINC;
            if(($Column->Flags & PDOColumn::FLAG_AUTOINC) && !$primaryCol) {
                $Column->Flags |= PDOColumn::FLAG_PRIMARY;
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
