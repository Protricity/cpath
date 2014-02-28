<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Builders;

use CPath\Framework\PDO\DB\MySQLDatabase;
use CPath\Framework\PDO\Table\Builders\Interfaces\IPDOTableBuilder;

use CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Interfaces\IBuildable;

class BuildMySQLTables extends BuildPDOTables implements IBuildable {

    const TMPL_TABLE_CLASS = <<<PHP
<?php
namespace %s;
use CPath\Framework\PDO\MySQLTable;
class %s extends MySQLTable {
%s}
PHP;

    /**
     * Builds class references for existing database tables
     * @param IBuildable $Buildable
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\Exceptions\BuildException when a build exception occurred
     */
    public function build(IBuildable $Buildable) {
        if(!$Buildable instanceof MySQLDatabase)
            return false;
        return parent::build($Buildable);
    }

    /**
     * @param \PDO $DB
     * @param $namespace
     * @return IPDOTableBuilder[]
     */
    protected function getTables(\PDO $DB, $namespace) {
        $tables = array();
        foreach($DB->query("SHOW TABLE STATUS") as $row) {
            $tables[$row['Name']] = $this->createTable($row['Name'], $row['Comment']);
        }
        return $tables;
    }

    /**
     * @param \PDO $DB
     * @param IPDOTableBuilder $Table
     * @return void
     */
    protected function getIndexes(\PDO $DB, IPDOTableBuilder $Table) {
        foreach($DB->query("SHOW KEYS FROM $Table;") as $row) {
            $name = $row['Column_name'];
            /** @var \CPath\Framework\PDO\Table\Column\Builders\Interfaces\IPDOColumnBuilder $Column */
            $Column = $Table->getColumns()->get($name);
            $Column->setFlag(PDOColumn::FLAG_INDEX);
            if(stripos($row['Key_name'], 'PRIMARY') === 0)
                $Column->setFlag(PDOColumn::FLAG_PRIMARY);
        }
    }

    /**
     * @param \PDO $DB
     * @param IPDOTableBuilder $Table
     * @return void
     */
    protected function getColumns(\PDO $DB, IPDOTableBuilder $Table) {
        foreach($DB->query("SHOW FULL COLUMNS FROM $Table;") as $row) {
            $name = $row['Field'];
            $Column = new BuildPDOColumn($name, $row['Comment']);
            if($row['Null'] == 'YES')
                $Column->mFlags |= PDOColumn::FLAG_NULL;
            if(preg_match('/^enum\((.*)\)$/', $row['Type'], $matches)) {
                $Column->mEnumValues = array();
                foreach( explode(',', $matches[1]) as $value )
                    $Column->mEnumValues[] = trim( $value, "'" );
                $Column->mFlags |= PDOColumn::FLAG_ENUM;
            } else {
                if(stripos($row['Type'], 'int') !== false)
                    $Column->mFlags |= PDOColumn::FLAG_NUMERIC;
            }
            if($row['Extra'] == 'auto_increment')
                $Column->mFlags |= PDOColumn::FLAG_AUTOINC;
            if($row['Default'] !== NULL)
                $Column->mFlags |= PDOColumn::FLAG_DEFAULT;
            $Table->addColumn($Column);
        }
    }

    protected function getProcs(\PDO $DB) {
        $procs = array();
        foreach($DB->query(
                    "SELECT r.routine_name"
                        ."  FROM information_schema.routines r"
                        ."  LEFT JOIN information_schema.parameters p on r.specific_name = p.specific_name"
                        ."  WHERE p.parameter_mode = 'IN'"
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
