<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\DB;
use CPath\Framework\PDO\Query\MySQLInsert;
use CPath\Framework\PDO\Query\SQLiteInsert;
use CPath\Framework\PDO\Table\Types\PDOTable;
use PDO;

abstract class SQLiteDatabase extends PDODatabase {
    const FUNC_FORMAT = "SELECT %s"; // TODO: remove?
    const VERSION_TABLE_NAME = "__cpath_getversion";

    protected $mConfig = array();
    public function __construct($prefix, $filename, $username=NULL, $password=NULL) {

        $filename = realpath($filename);
        $this->mConfig = array(
            'prefix' => $prefix,
            'filename' => $filename,
            'username' => $username,
            'password' => $password,
        );

        parent::__construct($prefix, "sqlite: " . $filename, $username, $password);

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getDBVersion() {
        try{
            return
                $this->query("Select version from " . self::VERSION_TABLE_NAME . " LIMIT 1")
                    ->fetchColumn(0);
        } catch (\PDOException $ex) {
            if(strpos($ex->getMessage(), 'does not exist') === false)
                throw $ex;
            $this->setDBVersion(0);
            return 0;
        }
    }

    public function setDBVersion($version) {
        $this->exec("DROP TABLE IF EXISTS " . self::VERSION_TABLE_NAME);
        $this->exec("CREATE TABLE " . self::VERSION_TABLE_NAME . " (version INTEGER)");
        $this->exec("INSERT INTO TABLE " . self::VERSION_TABLE_NAME . " (version) VALUES (" . $version . ")");
        return $this;
    }

    public function insert(PDOTable $Table, $_fieldArgs) {
        $args = is_array($_fieldArgs) ? $_fieldArgs : array_slice(func_get_args(), 1);
        return new SQLiteInsert($Table, $this, $args);
    }
}

