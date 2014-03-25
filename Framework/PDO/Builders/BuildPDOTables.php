<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Builders;

use CPath\Exceptions\BuildException;
use CPath\Framework\Build\API\Build;
use CPath\Framework\Build\IBuilder;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Builders\Tables\BuildPDOPKTable;
use CPath\Framework\PDO\DB\PDODatabase;
use CPath\Framework\PDO\Table\Builders\BuildPDOTable;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Builders\Interfaces\IPDOTableBuilder;
use CPath\Framework\PDO\Table\Builders\Templates\User\BuildPDOUserRoleTable;
use CPath\Framework\PDO\Table\Builders\Templates\User\BuildPDOUserSessionTable;
use CPath\Framework\PDO\Table\Builders\Templates\User\BuildPDOUserTable;
use CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn;
use CPath\Log;

abstract class BuildPDOTables implements IBuilder
{

    const TAB = null;

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

    private $mBuildDB = NULL;

    // TODO: move out of the build class
    public function upgrade(PDODatabase $DB, $oldVersion = NULL)
    {
        if ($oldVersion === NULL)
            $oldVersion = $DB->getDBVersion();
        $curVersion = $DB::VERSION;
        $Class = new \ReflectionClass($DB);
        $schemaFolder = $this->getFolder($Class, 'Schema');
        $files = scandir($schemaFolder);
        if (!$files)
            throw new UpgradeException("No Schemas found in " . $schemaFolder);
        $schemas = array();
        foreach ($files as $file) {
            if (in_array($file, array('.', '..')))
                continue;
            if (!is_file($schemaFolder . '/' . $file))
                continue;
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (!is_numeric($name))
                continue;
            //throw new UpgradeException("File '{$file}' is not numeric");
            $name = (int)$name;
            if ($name <= $oldVersion)
                continue;
            $schemas[$name] = $file;
        }
        if (!$schemas)
            throw new UpgradeException("New Version Number, but no new schemas found");
        ksort($schemas);
        foreach ($schemas as $v => $schema) {
            $sql = file_get_contents($schemaFolder . '/' . $schema);
            if (!$sql)
                throw new UpgradeException("Invalid SQL in " . $schema);

            $statusTable = '__cpath_dump_complete_' . rand(1, 99);
            $sql .= "\nCREATE TABLE $statusTable (status int);\nINSERT INTO $statusTable (status) VALUES (1)";
            //$sql = "\nSET @_cpath_dump_complete = " . ($id-1) . ";\n" . $sql;
            $DB->exec($sql);
            $status = $DB->query("SELECT status from $statusTable;")->fetchColumn(0);
            if (!$status)
                throw new BuildException("FATAL ERROR: Database dump failed (Status table was not created). Please check your schema sql syntax");

            $DB->exec("DROP TABLE $statusTable;");

            Log::v2(__CLASS__, "DB ID Result: $status");

            $DB->setDBVersion($v);


//            $id = rand(1,99);
//            $DB->exec("SET @_cpath_dump_complete=-1");
//            $sql .= "SET @_cpath_dump_complete=" . $id . ";\n";
//            //$sql = "\nSET @_cpath_dump_complete = " . ($id-1) . ";\n" . $sql;
//            $DB->exec($sql);
//            $id2 = $DB->query("SELECT @_cpath_dump_complete;")->fetchColumn(0);
//            if($id != $id2)
//                throw new BuildException("FATAL ERROR: Database dump failed (id mismatch {$id} != {$id2}). Please check your schema sql syntax");
//
//            Log::v2(__CLASS__, "DB ID Result: {$id} == {$id2}" );
//
//            $DB->setDBVersion($v);
        }
        Log::v(__CLASS__, "Upgraded Database from version $oldVersion to $curVersion.");
    }

    /**
     * @param \PDO $DB
     * @param $namespace
     * @return IPDOTableBuilder[]
     */
    protected abstract function getTables(\PDO $DB);

    /**
     * @param \PDO $DB
     * @param IPDOTableBuilder $Table
     * @return void
     */
    protected abstract function getColumns(\PDO $DB, IPDOTableBuilder $Table);

    /**
     * @param \PDO $DB
     * @param IPDOTableBuilder $Table
     * @return void
     */
    protected abstract function getIndexes(\PDO $DB, IPDOTableBuilder $Table);


    protected abstract function getProcs(\PDO $DB);


    public function createTable($name, $comment)
    {

        if (!($DB = $this->mBuildDB))
            throw new \Exception("No DB Instance for table build");

        $Table = new BuildPDOTable($DB, $name, $comment);

        $this->getColumns($DB, $Table);
        $this->getIndexes($DB, $Table);
        $Table->init();

        $Columns = $Table->getColumns();
        $isPrimary = false;
        foreach($Columns as $Column)
            if($Column->hasFlag(IPDOColumn::FLAG_PRIMARY))
                $isPrimary = true;

        if ($Table->getTemplateID()) {
            switch (strtolower($Table->getTemplateID())) {
                case 'u':
                case 'user':
                    Log::v(__CLASS__, "Table identified as template 'User': " . $name);
                    $Table = new BuildPDOUserTable($DB, $name, $comment);
                    break;
                case 'us':
                case 'usersession':
                    Log::v(__CLASS__, "Table identified as template 'User Session': " . $name);
                    $Table = new BuildPDOUserSessionTable($DB, $name, $comment);
                    break;
                case 'ur':
                case 'userrole':
                    Log::v(__CLASS__, "Table identified as template 'User Role': " . $name);
                    $Table = new BuildPDOUserRoleTable($DB, $name, $comment);
                    break;
                default:
                    throw new BuildException("Could not locate table template: " . $Table->getTemplateID());
            }
        } elseif ($isPrimary) {
            Log::v2(__CLASS__, "Table identified as Primary Key table: " . $name);
            $Table = new Tables\BuildPDOPKTable($DB, $name, $comment);
        } else {
            return $Table;
        }

        $this->getColumns($DB, $Table); // Redo for new table
        $this->getIndexes($DB, $Table);
        $Table->init();

        return $Table;
    }

    /**
     * Builds class references for existing database tables
     * @param \CPath\Framework\PDO\DB\PDODatabase $DB
     * @param $flags
     * @return boolean True if the class was built. False if it was ignored.
     */
    protected function buildClass(PDODatabase $DB, $flags=0)
    {
        $this->mBuildDB = $DB;

        $BUILD = $DB::BUILD_DB;
//        if (!in_array($BUILD, array('ALL', 'MODEL', 'PROC'))) {
//            Log::v(__CLASS__, "(BUILD_DB = {$BUILD}) Skipping Build for " . get_class($Buildable));
//            return false;
//        }

        $Class = new \ReflectionClass($DB);

        $tablePath = $this->getFolder($Class, 'Table');
        //$tableNS = $Class->getNamespaceName() . "\\Table";
        $modelPath = $this->getFolder($Class, 'Model');
        //$modelNS = $Class->getNamespaceName() . "\\Model";
        $procPath = $this->getFolder($Class, 'Procs');
        //$procNS = $Class->getNamespaceName() . "\\Procs";

        $Config =& Build::get()->getConfig($Class->getName());
        $schemaFolder = $this->getFolder($Class, 'Schema');
        $hash = 0;
        $force = Build::get()->force();

        $oldFiles = array();
        if (!file_exists($procPath)) {
            mkdir($procPath, null, true);
            $force = true;
        }
        if (!file_exists($modelPath)) {
            mkdir($modelPath, null, true);
            $force = true;
//        } else {
//          $oldFiles = array_diff(array_merge(scandir($modelPath), scandir($modelPath)), array('..', '.'));
        }
        if (!file_exists($tablePath)) {
            mkdir($tablePath, null, true);
            $force = true;
        }

        foreach (scandir($schemaFolder) as $modelClassFile)
            $hash += filemtime($schemaFolder . $modelClassFile);
        if (!$force && isset($Config['schemaHash']) && $Config['schemaHash'] == $hash) {
            Log::v(__CLASS__, "Skipping Build for " . $Class->getName());
            return false;
        }
        $Config['schemaHash'] = $hash;


        // Tables

        $tables = array();
        if (in_array($BUILD, array('ALL', 'MODEL')))
            $tables = $this->getTables($DB);

        $tableNames = array();
        foreach ($tables as $Table)
            $tableNames[] = $Table->getTableName();

        Log::v(__CLASS__, "Found Tables (%s): " . implode(', ', $tableNames), count($tableNames));

        // Model

        $noPrimary = array();

        foreach ($tables as $Table) {
            $modelClassFile = $modelPath . basename($Table->getModelClass()) . '.php';
            $tableClassFile = $tablePath . basename($Table->getTableClass()) . '.php';

            $PHPModel = new BuildPHPModelClass($Table->getModelClass(), $modelClassFile);

            $PHPTable = new BuildPHPTableClass($Table->getTableClass(), $tableClassFile);
            //$PHPTable->setExtend("CPath\\Model\\DB\\PDOTable");

            $Table->processPHP($DB, $PHPTable, $PHPModel);

            if (!$Table instanceof BuildPDOPKTable) // TODO: hacky?
                $noPrimary[] = $Table;

            //$PHPModel->addConst('PRIMARY', $Table->Primary);
//
//            $columns = "\n\t\tstatic \$columns = NULL;";
//            $columns .= "\n\t\treturn \$columns ?: \$columns = array(";
//            $i = 0;
//            foreach ($Table->getColumns() as $Column) {
//                if ($i++) $columns .= ',';
//                $columns .= "\n\t\t\t" . var_export($Column->Name, true) . ' => new PDOColumn(';
//                $columns .= var_export($Column->Name, true);
//                $columns .= ',0x' . dechex($Column->Flags ? : 0);
//
//                if ($Column->Comment || $Column->Filter || $Column->Default || $Column->EnumValues)
//                    $columns .= ',' . ($Column->Filter ? : 0);
//                if ($Column->Comment || $Column->Default || $Column->EnumValues)
//                    $columns .= ',' . var_export($Column->Comment ? : '', true);
//                if ($Column->Default || $Column->EnumValues)
//                    $columns .= ',' . var_export($Column->Default ? : '', true);
//                if ($Column->EnumValues) {
//                    $a = '';
//                    foreach ($Column->EnumValues as $e)
//                        $a .= ($a ? ',' : '') . var_export($e, true);
//                    $columns .= ',array(' . $a . ')';
//                }
//                $columns .= ")";
//            }
//            $columns .= "\n\t\t);\n";
//
//            //$PHP->addStaticMethod('init', NULL, $columns, 'public', false);
//            //$PHP->addStaticMethod('getColumns', NULL, ' return self::$_columns; ', 'protected', false);
//            $PHPTable->addStaticMethod('loadAllColumns', NULL, $columns, '', false);
//            //$PHP->addStaticProperty('_columns', NULL, 'private');
//            $PHPTable->addUse('CPath\Framework\PDO\PDOColumn');
//
//            if ($Table->SearchWildCard)
//                $PHPTable->addConst('SEARCH_WILDCARD', true);
//            if ($Table->SearchLimit)
//                $PHPTable->addConst('SEARCH_LIMIT', $Table->SearchLimit);
//            if ($Table->SearchLimitMax)
//                $PHPTable->addConst('SEARCH_LIMIT_MAX', $Table->SearchLimitMax);
//            if ($Table->AllowHandler)
//                $PHPTable->addImplements('CPath\Interfaces\IBuildable');
            //$PHP->addConst('BUILD_IGNORE', false);

            // Table
//
//            $PHPTable->addConstCode();
//            $PHPTable->addConstCode("// Table Columns ");
//            foreach ($Table->getColumns() as $Column)
//                $PHPTable->addConst(PDOStringUtil::toTitleCase($Column->Name, true), $Column->Name);
//
//            foreach ($Table->getColumns() as $Column)
//                if ($Column->EnumConstants) {
//                    $PHPTable->addConstCode();
//                    $PHPTable->addConstCode("// Column Enum Values for '" . $Column->Name . "'");
//                    foreach ($Column->EnumValues as $enum)
//                        $PHPTable->addConst(PDOStringUtil::toTitleCase($Column->Name, true) . '_Enum_' . PDOStringUtil::toTitleCase($enum, true), $enum);
//                }
//
//            if ($Table->Primary) // TODO: primary hack needs oop
//                $PHPModel->addStaticMethod('remove', $Table->ModelClassName . ' $' . $Table->ModelClassName, " parent::removeModel(\${$Table->ModelClassName}); ");

            // Models
//
//            foreach ($Table->getColumns() as $Column)
//                $PHPModel->addProperty($Column->Name);

//            foreach ($Table->getColumns() as $Column) {
//                $ucName = PDOStringUtil::toTitleCase($Column->Name, true);
//                $PHPModel->addMethod('get' . $ucName, '', sprintf(' return $this->%s; ', strtolower($Column->Name)));
//                if ($Column->Flags & PDOColumn::FLAG_PRIMARY ? 0 : 1 && $Table->Primary) // TODO: primary hack needs oop
//                    $PHPModel->addMethod('set' . $ucName, '$value, $commit=true', sprintf(' return $this->updateColumn(\'%s\', $value, $commit); ', strtolower($Column->Name)));
//                $PHPModel->addMethodCode();
//            }
//
//
//            $PHPTable->addUse(get_class($DB), 'DB');
//            $PHPTable->addStaticMethod('getDB', '', " return DB::get(); ");


            //Log::v2(__CLASS__, "Writing file: " . $file);
            //file_put_contents($modelClassFile, $PHPModel->build());
            $PHPModel->write();
            foreach ($oldFiles as $i => $f)
                if ($modelPath . $f == $modelClassFile && !is_dir($modelPath . $f)) {
                    unset($oldFiles[$i]);
                    break;
                }

            $PHPTable->write();
            //file_put_contents($tableClassFile, $PHPTable->build());
            foreach ($oldFiles as $i => $f)
                if ($tablePath . $f == $tableClassFile && !is_dir($tablePath . $f)) {
                    unset($oldFiles[$i]);
                    break;
                }

            // CSharp

            $fileCSharp = $modelPath . 'CSharp/' . ($Table->getModelClass()) . '.cs';
            if (!file_exists($dir = dirname($fileCSharp)))
                mkdir($dir, null, true);
            $CBuilder = new BuildCSharpTables($DB::BUILD_DB_CSHARP_NAMESPACE);
            $CBuilder->build($Table, $fileCSharp);

            $this->mBuildDB = NULL;
        }

        if ($noPrimary) {
            $t = array();
            /** @var BuildPDOTable $Table */
            foreach ($noPrimary as $Table)
                $t[] = $Table->getTableName();
            Log::e(__CLASS__, "No PRIMARY key found for (" . count($noPrimary) . ") Table(s) '" . implode("', '", $t) . "'");
        }

        //Log::v(__CLASS__, "Built (".sizeof($tables).") table definition class(es)");
        Log::v(__CLASS__, "Built (" . sizeof($tables) . ") table model(s)");
        if ($c = sizeof($oldFiles)) {
            Log::v(__CLASS__, "Removing ({$c}) depreciated model classes");
            foreach ($oldFiles as $modelClassFile)
                if (!is_dir($modelPath . $modelClassFile))
                    unlink($modelPath . $modelClassFile);
        }

        // Stored Procedures

//        $procs = array();
//        if (in_array($BUILD, array('ALL', 'PROC')))
//            $procs = $this->getProcs($DB);
//
//        $PHPProcs = new BuildPHPClass('Procs', $procNS);
//        $PHPProcs->addUse(get_class($DB), 'DB');
//        $names = array();
//        foreach ($procs as $proc) {
//            $name = array_shift($proc);
//            if (isset($names[$name])) {
//                $name .= ++$names[$name];
//            } else {
//                $names[$name] = 1;
//            }
//            $method = $name . '(' . (!$proc ? '' : ('%s' . str_repeat(', %s', sizeof($proc) - 1))) . ')';
//            $PHPProcs->addConst(strtoupper($name), $method);
//
//            $sqlParams = $proc ? '?' . str_repeat(', ?', sizeof($proc) - 1) : '';
//            $codeParams = $proc ? '$' . implode(', $', $proc) : '';
//
//            $code = <<<PHP
//        static \$stmd = NULL;
//        if(!\$stmd) \$stmd = \$this->getDB()->prepare('SELECT $name({$sqlParams})');
//        \$stmd->execute(array({$codeParams}));
//        return \$stmd;
//PHP;
//
//            $ucName = PDOStringUtil::toTitleCase($name, true);
//            $ucName[0] = strtolower($ucName[0]);
//            $PHPProcs->addStaticMethod($ucName, $proc, $code);
//            //$phpC .= self::getConst(strtoupper($name), $method);
//        }
//
//        $PHPProcs->addMethod('getDB', '', " return DB::get(); ");
//        //$php = sprintf(self::TMPL_PROC_CLASS, $procNS, $phpC.$phpP);
//        //file_put_contents($procPath.'procs.class.php', $php);
//        file_put_contents($procPath . 'Procs.php', $PHPProcs->build());
//        Log::v(__CLASS__, "Built (" . sizeof($procs) . ") routine(s)");

        return true;
    }

    /**
     * Unused
     */
    public function buildComplete()
    {

    }

    private function getFolder(\ReflectionClass $Class, $subFolder = NULL)
    {
        if ($subFolder) $subFolder .= '/';
        return dirname($Class->getFileName()) . '/' . $subFolder;
    }
}

class UpgradeException extends \Exception {}

