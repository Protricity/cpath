<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use \PDO;
use CPath\Builders\BuildPGTables;
abstract class MySQLDatabase extends PDODatabase {
    const BUILD_TABLE_PATH = 'tables';
    const FUNC_FORMAT = "SELECT %s";
    protected $mConfig = array();
    public function __construct($prefix, $database, $host=NULL, $username=NULL, $password=NULL) {
        $this->mConfig = get_defined_vars();

        parent::__construct($prefix, "mysql:dbname=$database;host=$host", $username, $password );
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getDBVersion() {
        try{
            return
                $this->query("Select _getVersion();")
                    ->fetchColumn(0);
        } catch (\PDOException $ex) {
            if(strpos($ex->getMessage(), 'does not exist') === false)
                throw $ex;
            $this->setDBVersion(0);
            return 0;
        }
    }

    public function setDBVersion($version) {
        $this->exec('DROP FUNCTION IF EXISTS _getVersion');
        $this->exec('CREATE FUNCTION `_getVersion` () RETURNS INTEGER BEGIN RETURN '.((int)$version).'; END ;');
        return $this;
    }

    public function insert($tableName, $_fieldArgs) {
        $args = func_get_args();
        return new MySQLInsert($tableName, $this, array_slice($args, 1));
    }
}

