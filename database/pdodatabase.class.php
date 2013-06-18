<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
use CPath\Base;
use CPath\Builders\BuildPGTables;

class SQLException extends \Exception {}
abstract class PDODatabase Extends DataBase {
    const FUNC_FORMAT = NULL;

    private $mPDO;

    protected function __construct($prefix, $dsn, $username, $passwd, $options=NULL) {
        $this->mPDO = new \PDO($dsn, $username, $passwd, $options);
        parent::__construct($prefix);
    }

    /**
     * Returns the PDO database instance
     * @return \PDO Database instance
     */
    public function getPDO() { return $this->mPDO; }

    abstract function getDBVersion();
    //abstract function insert($table, Array $pairs);
    //abstract function insertOrUpdate($table, Array $pairs, Array $updatePairs);

    protected abstract function setDBVersion($version);

    public function query($sql) {
        try{
            return $this->getPDO()->query($sql);
        } catch (\PDOException $ex) {
            throw new SQLException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function exec($sql) {
        try{
            return $this->getPDO()->exec($sql);
        } catch (\PDOException $ex) {
            throw new SQLException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function quotef($format, $_args) {
        return $this->quotef($format, array_slice(func_get_args(), 1));
    }

    public function vquotef($format, Array $args) {
        foreach($args as &$arg) $arg = $this->mPDO->quote($arg);
        $ret = vsprintf($format, $args);
        if(!is_string($ret))
            throw new \Exception("Invalid quotef() format ($format) or number of parameters (".sizeof($args).")");
        return $ret;
    }

    public function upgrade() {
        $version = static::VERSION;
        if($version === NULL)
            throw new \Exception("Version Constant is missing");
        Base::log("Upgrading Database to $version");
        $version = (int)$version;
        $oldVersion = $this->getDBVersion();
        if($version <= $oldVersion)
            return $this;
        BuildPGTables::upgrade($this);
    }
}