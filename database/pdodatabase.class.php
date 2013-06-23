<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
use CPath\Base;
use CPath\Log;
use CPath\Builders\BuildPGTables;
use CPath\Interfaces\IDatabase;
use CPath\Interfaces\IDataBaseHelper;

abstract class PDODatabase extends \PDO implements IDataBase {
    use IDataBaseHelper;

    const FUNC_FORMAT = NULL;

    public function __construct($prefix, $dsn, $username, $passwd, $options=NULL) {
        $this->setPrefix($prefix);
        parent::__construct($dsn, $username, $passwd, $options);
    }

    abstract function getDBVersion();
    //abstract function insert($table, Array $pairs);
    //abstract function insertOrUpdate($table, Array $pairs, Array $updatePairs);

    protected abstract function setDBVersion($version);

    public function quotef($format, $_args) {
        return $this->vquotef($format, array_slice(func_get_args(), 1));
    }

    public function vquotef($format, Array $args) {
        foreach($args as &$arg)
            $arg = $this->quote($arg);
        $ret = vsprintf($format, $args);
        if(!is_string($ret))
            throw new \Exception("Invalid quotef() format ($format) or number of parameters (".sizeof($args).")");
        return $ret;
    }

    public function upgrade() {
        $version = static::VERSION;
        if($version === NULL)
            throw new \Exception("Version Constant is missing");
        $version = (int)$version;
        $oldVersion = $this->getDBVersion();
        if($version <= $oldVersion){
            Log::v(__CLASS__, "Skipping Database Upgrade");
            return $this;
        }
        Log::v(__CLASS__, "Upgrading Database to $version");
        BuildPGTables::upgrade($this);
        return $this;
    }
}