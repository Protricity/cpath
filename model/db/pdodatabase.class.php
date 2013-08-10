<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Base;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IBuilder;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoutable;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IRouteBuilder;
use CPath\Log;
use CPath\Builders\BuildPGTables;
use CPath\Interfaces\IDatabase;
use CPath\Builders\RouteBuilder;
use CPath\Util;

class NotConfiguredException extends \Exception{}
abstract class PDODatabase extends \PDO implements IDataBase, IHandler, IRoutable {
    const Version = NULL;
    const Build_DB = 'NONE'; // ALL|MODEL|PROC|NONE;
    const Build_Table_Path = 'tables';
    const FUNC_FORMAT = NULL;

    const ROUTE_METHODS = 'CLI';   // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;       // No custom route path. Path is based on namespace + class name

    private $mPrefix;


    /**
     * @param $tableName
     * @param $_selectArgs
     * @return PDOSelect
     */
    public function select($tableName, $_selectArgs) {
        $args = is_array($_selectArgs) ? $_selectArgs : array_slice(func_get_args(), 1);
        return new PDOSelect($tableName, $this, $args);
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

    public function upgrade($rebuild=false) {
        $version = static::Version;
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
            throw new \Exception("Database Upgrade is disabled db.upgrade.enabled===false");
        $Build->upgrade($this, $oldVersion);
        return $this;
    }

    // Implement IHandler

    function render(IRequest $Request) {
        if(!Base::isCLI() && !headers_sent())
            header('text/plain');
        echo "DB Upgrader: ".get_class($this)."\n";
        switch(strtolower($Request->getNextArg())) {
            case 'upgrade':
                $this->upgrade(false);
                break;
            case 'rebuild':
                $this->upgrade(true);
                break;
            default:
                echo "use .../upgrade to upgrade database\n";
                break;
        }
        echo "DB Upgrader Completed\n";
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

    // Implement IRoutable

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     */
    function getAllRoutes(IRouteBuilder $Builder) {
        return $Builder->getHandlerDefaultRoutes(static::ROUTE_METHODS, static::ROUTE_PATH);
    }
}