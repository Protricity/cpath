<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
use \PDO;
use CPath\Interfaces\IBuilder;
use CPath\Builders\BuildPGTables;
abstract class PostGreSQL extends PDODatabase implements IBuilder {
    const BUILD_TABLE_PATH = 'tables';
    const FUNC_FORMAT = "SELECT %s";
    protected $mConfig = array();
    protected function __construct($prefix, $database, $host=NULL, $username=NULL, $password=NULL) {
        $this->mConfig = get_defined_vars();

        parent::__construct($prefix, "pgsql:dbname=$database;host=$host", $username, $password );
        $this->getPDO()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->getPDO()->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getDBVersion() {
        try{
            return
                $this->query("Select _getVersion();")
                    ->fetchColumn(0);
        } catch (SQLException $ex) {
            $this->setDBVersion(0);
            return 0;
        }
    }

    public function setDBVersion($version) {
        try{
            $this->exec('DROP FUNCTION _getVersion()');
        } catch (SQLException $ex) {}
        $this->exec('CREATE FUNCTION _getVersion() RETURNS int AS \'Select '.((int)$version).';\' LANGUAGE SQL;');
    }

//    function prepareInsert($table, Array $keys) {
//        return $this->getPDO()
//            ->prepare("INSERT INTO {$table} (".implode(',', $keys).") VALUES (:".implode(', :', $keys).")");
//    }
//
//    function prepareUpdate($table, Array $keys, $whereKey) {
//        foreach($keys as &$key)
//            $key = $key.'=:'.$key;
//        return $this->getPDO()
//            ->prepare("UPDATE {$table} SET ".implode(',', $keys)." WHERE $whereKey=:$whereKey");
//    }

    static function build(\ReflectionClass $Class)
    {
        BuildPGTables::build($Class);
    }

    static function buildComplete(\ReflectionClass $Class)
    {
        BuildPGTables::buildComplete($Class);
    }
}