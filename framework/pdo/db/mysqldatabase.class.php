<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\DB;
use CPath\Framework\PDO\Query\MySQLInsert;
use PDO;

abstract class MySQLDatabase extends PDODatabase {
    const FUNC_FORMAT = "SELECT %s";
    protected $mConfig = array();
    public function __construct($prefix, $database, $host=NULL, $username=NULL, $password=NULL) {
        $this->mConfig = get_defined_vars();
        unset($this->mConfig['this']);

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
        $args = is_array($_fieldArgs) ? $_fieldArgs : array_slice(func_get_args(), 1);
        return new MySQLInsert($tableName, $this, $args);
    }
}

