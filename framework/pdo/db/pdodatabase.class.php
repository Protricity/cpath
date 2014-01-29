<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\DB;
use CPath\Base;
use CPath\Build;
use CPath\Framework\PDO\Builders\BuildPGTables;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\PDOConfig;
use CPath\Framework\PDO\Query\PDODelete;
use CPath\Framework\PDO\Query\PDOInsert;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOUpdate;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IDatabase;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Log;
use CPath\Route\IRoute;
use CPath\Route\Route;

class NotConfiguredException extends \Exception {}

abstract class PDODatabase extends \PDO implements IDataBase, IHandler {
    const VERSION = NULL;
    const BUILD_DB = 'NONE'; // ALL|MODEL|PROC|NONE;
    const BUILD_DB_CSHARP_NAMESPACE = null;
    const BUILD_TABLE_PATH = 'tables';
    const FUNC_FORMAT = NULL;

    const ROUTE_METHOD = 'CLI';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;       // No custom route path. Path is based on namespace + class name

    private $mPrefix;


    /**
     * @param $tableName
     * @param $_selectArgs
     * @return PDOSelect
     */
    public function select($tableName, $_selectArgs, ISelectDescriptor $Descriptor=null) {
        $args = is_array($_selectArgs) ? $_selectArgs : array_slice(func_get_args(), 1);
        $Select = new PDOSelect($tableName, $this, $args, $Descriptor);
        if($Descriptor)
            $Select->setDescriptor($Descriptor);
        return $Select;
    }

    /**
     * @param $tableName
     * @param $_fieldArgs
     * @return PDOInsert
     */
    abstract public function insert($tableName, $_fieldArgs);

    /**
     * @param $tableName
     * @param $_selectArgs
     * @return PDOUpdate
     */
    public function update($tableName, $_selectArgs) {
        $args = is_array($_selectArgs) ? $_selectArgs : array_slice(func_get_args(), 1);
        return new PDOUpdate($tableName, $this, $args);
    }

    public function delete($tableName) {
        return new PDODelete($tableName, $this);
    }

    public function __construct($prefix, $dsn, $username, $passwd, $options=NULL) {
        $this->setPrefix($prefix);
        parent::__construct($dsn, $username, $passwd, $options);
    }

    protected function setPrefix($prefix) {
        $this->mPrefix = $prefix;
    }

    public function getPrefix() { return $this->mPrefix; }

    /**
     * @return PDODatabase
     * @throws NotConfiguredException
     */
    static function get()
    {
        throw new NotConfiguredException("Database helper ".get_called_class()."::get() is missing");
    }

    abstract function getDBVersion();
    //abstract function insert($table, Array $pairs);
    //abstract function insertOrUpdate($table, Array $pairs, Array $updatePairs);

    abstract function setDBVersion($version);

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

    // TODO: move to builder
    public function upgrade($rebuild=false, $force=false) {
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

        if(!PDOConfig::$UpgradeEnable && !$force)
            throw new \Exception("Database Upgrade is disabled Config::\$UpgradeEnable==false");
        $Build->upgrade($this, $oldVersion);
        return $this;
    }

    // Implement IHandler

    function render(IRequest $Request) {
        if(!Base::isCLI() && !headers_sent())
            header('text/plain');
        Log::u(__CLASS__, "DB Upgrader: ".get_class($this));
        switch(strtolower($Request->getNextArg())) {
            case 'upgrade':
                $this->upgrade(false, $Request['force']);
                break;
            case 'reset':
                $this->upgrade(true, $Request['force']);
                break;
            case 'rebuild':
                $this->upgrade(true, $Request['force']);
                $Build = new Build();
                Log::u(__CLASS__, "Rebuilding Models...");
                $Build->execute($Request);
                break;
            default:
                Log::u(__CLASS__, "Use 'upgrade', 'reset', or 'rebuild' to upgrade database");
                break;
        }
        Log::u(__CLASS__, "DB Upgrader Completed");
    }

    // Statics

    // Implement IBuildable (sub class still needs to have "implements IBuildable")

    /**
     * Return an instance of the class for building purposes
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance() {
        return static::get();
    }

    /**
     * Returns the route for this IHandler
     * @return IRoute
     */
    function loadRoute() {
        return Route::fromHandler($this, static::ROUTE_METHOD, static::ROUTE_PATH);
    }
}