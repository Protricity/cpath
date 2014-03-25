<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\DB;
use CPath\Framework\Build\API\Build;
use CPath\Framework\PDO\Builders\BuildPGTables;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\PDOConfig;
use CPath\Framework\PDO\Query\PDODelete;
use CPath\Framework\PDO\Query\PDOInsert;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOUpdate;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Interfaces\IDatabase;
use CPath\Log;

class NotConfiguredException extends \Exception {}

abstract class PDODatabase extends \PDO implements IDatabase, IRender {
    const VERSION = NULL;
    const BUILD_DB = 'NONE'; // ALL|MODEL|PROC|NONE;
    const BUILD_DB_CSHARP_NAMESPACE = null;
    const BUILD_TABLE_PATH = 'tables';
    const FUNC_FORMAT = NULL;

    const ROUTE_METHOD = 'CLI';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;       // No custom route path. Path is based on namespace + class name

    private $mPrefix;

    /**
     * @param \CPath\Framework\PDO\Table\Types\PDOTable $Table
     * @param $_selectArgs
     * @param ISelectDescriptor $Descriptor
     * @internal param $tableName
     * @return PDOSelect
     */
    public function select(PDOTable $Table, $_selectArgs, ISelectDescriptor $Descriptor=null) {
        $args = is_array($_selectArgs) ? $_selectArgs : array_slice(func_get_args(), 1);
        $Select = new PDOSelect($Table, $this, $args, $Descriptor);
        if($Descriptor)
            $Select->setDescriptor($Descriptor);
        return $Select;
    }

    /**
     * @param \CPath\Framework\PDO\Table\Types\PDOTable $Table
     * @param $_fieldArgs
     * @internal param $tableName
     * @return PDOInsert
     */
    abstract public function insert(PDOTable $Table, $_fieldArgs);

    /**
     * @param \CPath\Framework\PDO\Table\Types\PDOTable $Table
     * @param $_selectArgs
     * @internal param $tableName
     * @return PDOUpdate
     */
    public function update(PDOTable $Table, $_selectArgs) {
        $args = is_array($_selectArgs) ? $_selectArgs : array_slice(func_get_args(), 1);
        return new PDOUpdate($Table, $args);
    }

    public function delete(PDOTable $Table) {
        return new PDODelete($Table, $this);
    }

    public function __construct($prefix, $dsn, $username, $passwd, $options=NULL) {
        $this->setPrefix($prefix);
        parent::__construct($dsn, $username, $passwd, $options);
    }

    protected function setPrefix($prefix) {
        $this->mPrefix = $prefix;
    }

    public function getPrefix() { return $this->mPrefix; }


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

    // Implement IRender

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request)
    {
        $args = $Request->getArgs();
        if($args[0]) {
            $arg = array_shift($args);
            switch(strtolower($arg)) {
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
                    $Build->execute($Request, $args);
                    break;
                default:
                    Log::u(__CLASS__, "Use 'upgrade', 'reset', or 'rebuild' to upgrade database");
                    break;
            }
        }
        Log::u(__CLASS__, "DB Upgrader Completed");
    }

    // Statics

    /**
     * @return PDODatabase
     * @throws NotConfiguredException
     */
    static function get()
    {
        throw new NotConfiguredException("Database helper ".get_called_class()."::get() is missing");
    }
}