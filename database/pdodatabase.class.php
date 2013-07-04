<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
use CPath\Base;
use CPath\Interfaces\IBuilder;
use CPath\Interfaces\IHandler;
use CPath\Log;
use CPath\Builders\BuildPGTables;
use CPath\Interfaces\IDatabase;
use CPath\Builders\BuildRoutes;

class NotConfiguredException extends \Exception{}
abstract class PDODatabase extends \PDO implements IDataBase, IHandler {

    const FUNC_FORMAT = NULL;
    private $mPrefix;

    public function __construct($prefix, $dsn, $username, $passwd, $options=NULL) {
        $this->setPrefix($prefix);
        parent::__construct($dsn, $username, $passwd, $options);
    }

    protected function setPrefix($prefix) {
        $this->mPrefix = $prefix;
    }

    public function getPrefix() { return $this->mPrefix; }

    static function get()
    {
        throw new NotConfiguredException("Database helper ".get_called_class()."::get() is missing");
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

    public function upgrade($rebuild=false) {
        $version = static::VERSION;
        if($version === NULL)
            throw new \Exception("Version Constant is missing");
        $version = (int)$version;
        $oldVersion = $this->getDBVersion();
        if(!$rebuild && $version <= $oldVersion){
            Log::v(__CLASS__, "Skipping Database Upgrade (New={$version} Old={$oldVersion})");
            return $this;
        }
        if($rebuild) {
            $oldVersion = 0;
            Log::v(__CLASS__, "Rebuilding Database to {$version}");
        }

        Log::v(__CLASS__, "Upgrading Database from version {$oldVersion} to {$version}");
        $Build = new BuildPGTables();

        if(Base::getConfig('db.upgrade.enabled', true) === false)
            throw new \Exception("Database Upgrade is disabled db.upgrade.auto===false");
        $Build->upgrade($this, $oldVersion);
        return $this;
    }

    // Implement IHandler

    const ROUTE_METHODS = 'CLI';

    function render(Array $args) {
        header('text/plain');
        echo "DB Upgrader: ".get_class($this)."\n\n";
        if($args[0] == 'upgrade' || $args[0] == 'rebuild') {
            $this->upgrade($args[0] == 'rebuild');
            foreach(Log::get() as $log)
                echo $log."\n";
        } else {
            echo "use /upgrade to upgrade database";
        }
        echo "DB Upgrader Completed\n";

    }

}