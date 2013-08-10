<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;

use CPath\Base;
use CPath\Cache;
use CPath\Exceptions\ValidationException;
use CPath\Handlers\HandlerSet;
use CPath\Interfaces\IArrayObject;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IXML;
use CPath\Log;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Model\Response;
use CPath\Validate;

interface IGetDB {
    /**
     * @return PDODatabase
     */
    static function getDB();
}

class ModelNotFoundException extends \Exception {}
class ModelAlreadyExistsException extends \Exception {}
class ColumnNotFoundException extends \Exception {}
class InvalidPermissionException extends \Exception {}

abstract class PDOModel implements IResponseAggregate, IGetDB, IJSON, IXML, IBuildable {
    //const BUILD_IGNORE = true;
    //const ROUTE_METHODS = 'GET|POST|CLI';     // Default accepted methods are GET and POST

    const TABLE = null;
    const MODEL_NAME = null;
    const PRIMARY = null;
    const VALIDATIONS = null;

    const SEARCH_LIMIT_MAX = 100;
    const SEARCH_LIMIT = 25;
    const SEARCH_WILDCARD = false;   // true or false

    const CACHE_ENABLED = false;
    const CACHE_TTL = 300;

    const HANDLER_ID = NULL;   // Identifier column or list of columns for such endpoints as GET, PATCH, DELETE
    const SEARCH = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';
    const INSERT = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';
    const UPDATE = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';
    const EXPORT = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';

    const DEFAULT_FILTER =   FILTER_SANITIZE_SPECIAL_CHARS;

    const SECURITY_DISABLED = false;

    //protected $mRow = null;
    private $mCommit = NULL;

    /**
     * PDOModel Constructor parameters must be optional.
     * No queries should be attempted to load the model from the constructor.
     * Parameters may formatted and additional parameters added in the constructor
     */
    public function __construct() {

    }

    /**
     * Get model value by column
     * @param String $column column name
     * @return mixed
     */
    function columnValue($column) {
        $this->loadColumn($column);
        return $this->$column;
    }

    /**
     * UPDATE a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    function updateColumn($column, $value, $commit=true) {
        if($this->$column == $value)
            return $this;
        if(!$this->mCommit)
            $this->mCommit = array();
        $this->mCommit[$column] = $value;
        if($commit)
            $this->commitColumns();
        $this->$column = $value;
        return $this;
    }

    /**
     * UPDATE column values for this Model
     * @return int the number of columns updated
     * @throws \Exception if no primary key exists
     */
    function commitColumns() {
        if(!($primary = static::PRIMARY))
            throw new \Exception("Constant 'PRIMARY' is not set. Cannot UPDATE table without it");
        $id = $this->$primary;
        if(!$this->mCommit) {
            Log::u(get_called_class(), "No Fields Updated for ".static::modelName()." '{$id}'");
            return 0;
        }
        $set = '';
        $DB = static::getDB();
        foreach($this->mCommit as $column=>$value)
            $set .= ($set ? ",\n\t" : '') . "{$column} = ".$DB->quote($value);
        $SQL = "UPDATE ".static::TABLE
            ."\n SET {$set}"
            ."\n WHERE ".static::PRIMARY." = ".$DB->quote($id);
        $DB->exec($SQL);
        Log::u(get_called_class(), "Updated " . $this);
        $c = sizeof($this->mCommit);
        $this->mCommit = array();
        if(static::CACHE_ENABLED)
            static::$mCache->store(get_called_class() . ':id:' . $id, $this, static::CACHE_TTL);
        return $c;
    }


    function toXML(\SimpleXMLElement $xml){
        foreach($this->exportData() as $key=>$val)
            $xml->addAttribute($key, $val);

    }

    function toJSON(Array &$JSON){
        foreach($this->exportData() as $key=>$val)
            $JSON[$key] = $val;
    }

    /**
     * Returns an associative array of columns and values for this object filtered by the tokens in constant EXPORT.
     * Defaults to just primary key, if exists.
     * Modify const EXPORT to change what data gets exported
     * @return Array
     */
    public function exportData()
    {
        $export = array();
        foreach(static::findColumns(static::EXPORT ?: PDOColumn::FlagExport) as $column => $data)
            $export[$column] = $this->$column;
        return $export;
    }

    /**
     * @return IResponseAggregate
     */
    public function createResponse() {
        return new Response("Retrieved " . $this, true, $this);
    }

    public function __toString() {
        if($id = static::HANDLER_ID ?: static::PRIMARY)
            return static::modelName() . " '" . $this->$id . "'";
        return static::modelName();
    }

    /**
     * Returns the default IHandlerSet collection for this PDOModel type.
     * @param HandlerSet $Handlers a set of handlers to add to, otherwise a new HandlerSet is created
     * @return HandlerSet a set of common handler routes for this PDOModel type
     */
    function loadDefaultHandlers(HandlerSet $Handlers=NULL) {
        if($Handlers === NULL)
            $Handlers = new HandlerSet($this);

        $Handlers->add('GET', new API_Get($this));
        $Handlers->add('GET search', new API_GetSearch($this));
        $Handlers->add('POST', new API_Post($this));
        $Handlers->add('PATCH', new API_Patch($this));
        $Handlers->add('DELETE', new API_Delete($this));

        return $Handlers;
    }

    // Statics

    /**
     * @var Cache
     */
    protected static $mCache = NULL;

    /**
     * Initialize this class
     */
    public static function init() {
        self::$mCache = Cache::get();
    }

    /**
     * Return all columns for this Model
     * @return PDOColumn[]
     */
    static function loadAllColumns() { return array(); }

    /**
     * Return information for a column
     * @param String $name the name of the column
     * @return PDOColumn
     * @throws ColumnNotFoundException if the column was not found
     */
    static function loadColumn($name) {
        $cols = static::loadAllColumns();
        if(!isset($cols[$name]))
            throw new ColumnNotFoundException("Column '{$name}' could not be found in " . static::modelName());
        return $cols[$name];
    }

    /**
     * Returns an indexed array of column names for this object filtered by the tokens in constant EXPORT.
     * @param $tokens String|Array|int the column list (comma delimited), array, token or filter to use, or NULL for all columns
     * @return PDOColumn[] associative array of $columns and config data
     * @throws \Exception if an invalid token was used
     */
    static function findColumns($tokens) {
        if(is_int($tokens)) {
            $list = array();
            foreach(static::loadAllColumns() as $col => $data)
                if($data->isFlag($tokens))
                    $list[$col] = $data;
            return $list;
        }
        if(!is_array($tokens)) {
            if($tokens[0] == ':') {
                $tokens = explode(':', $tokens);
                switch(strtolower($tokens[1])) {
                    case ':none':
                        return array();
                    case ':index':
                        return static::findColumns(PDOColumn::FlagIndex);
                    case ':sindex':
                        $list = array();
                        foreach(static::loadAllColumns() as $col => $data)
                            if($data->isFlag(PDOColumn::FlagIndex) && !$data->isFlag(PDOColumn::FlagNumeric))
                                $list[$col] = $data;
                        return $list;
                    case ':primary':
                        return static::findColumns(PDOColumn::FlagPrimary);
                    case ':exclude':
                        if(empty($tokens[2]) || !($tokens = explode(',', $tokens[2])))
                            return array();
                        return array_diff_key(static::loadAllColumns(), array_flip($tokens));
                    case ':include':
                        if(empty($tokens[2]) || !($tokens = explode(',', $tokens[2])))
                            return array();
                        return array_intersect_key(static::loadAllColumns(), array_flip($tokens));
                    default:
                        throw new \Exception("Invalid Identifier: " . $tokens[1]);
                }
            }

            if(!($tokens = explode(',', $tokens)))
                return array();
        }
        return array_intersect_key(static::loadAllColumns(), array_flip($tokens));
    }
//
//    /**
//     * Validate a request of column values using compiled configuration
//     * @param IRequest $Request the IRequest instance to validate
//     * @throws ValidationException
//     */
//    static function validateRequest(IRequest $Request) {
//        foreach($Request as $column=>$value)
//            static::getColumn($column)
//                ->validate($value);
//    }

    /**
     * Creates a new Model based on the provided row of column value pairs
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOModel|null returns NULL if no primary key column is available
     * @throws ModelAlreadyExistsException
     * @throws \Exception|\PDOException
     * @throws ValidationException if a column fails to validate
     */
    public static function createFromArray($row) {
        if($row instanceof IArrayObject)
            $row = $row->getDataPath();
        foreach($row as $k=>$v)
            if($v===null)
                unset($row[$k]);
        try {
//            foreach($row as $column => $value) // No re-validation
//                static::validateColumn($column, $value);

            if(!static::PRIMARY) {
                static::insert(array_keys($row))
                    ->values(array_values($row));
                return NULL;
            } else {
                if(isset($row[static::PRIMARY]))
                    $id = $row[static::PRIMARY];
                $Insert = static::insert(array_keys($row))
                    ->requestInsertID(static::PRIMARY)
                    ->values(array_values($row));
                if(!isset($id))
                    $id = $Insert->getInsertID();
                $Model = static::loadByPrimaryKey($id);
                Log::u(get_called_class(), "Created " . $Model);
                return $Model;
            }
        } catch (\PDOException $ex) {
            if(stripos($ex->getMessage(), 'Duplicate')!==false) {
                $err = "A Duplicate ".static::modelName()." already exists";
                if(Base::isDebug())
                    $err .= ': ' . $ex->getMessage();
                Log::u(get_called_class(), "Duplicate ".static::modelName()." already exists");
                throw new ModelAlreadyExistsException($err, $ex->getCode(), $ex);
            }
            throw $ex;
        }
    }

    // Database methods

    /**
     * Create a PDOModelSelect object for this table
     * @param $_selectArgs array|mixed an array or series of varargs of columns to select
     * @return PDOModelSelect
     */
    static function select($_selectArgs) {
        $args = is_array($_selectArgs) ? $_selectArgs : func_get_args();
        return new PDOSelect(static::TABLE, static::getDB(), $args);
    }

    /**
     * Create a PDOInsert object for this table
     * @param $_insertArgs array|mixed an array or series of varargs of columns to insert
     * @return PDOInsert
     */
    static function insert($_insertArgs) {
        $DB = static::getDB();
        $args = is_array($_insertArgs) ? $_insertArgs : func_get_args();
        return $DB->insert(static::TABLE, $args);
    }

    /**
     * Create a PDOUpdate object for this table
     * @param $_columnArgs array|mixed an array or series of varargs of columns to be updated
     * @return PDOUpdate
     */
    static function update($_columnArgs) {
        $args = is_array($_columnArgs) ? $_columnArgs : func_get_args();
        return new PDOUpdate(static::TABLE, static::getDB(), $args);
    }

    /**
     * Create a PDODelete object for this table
     * @return PDODelete
     */
    static function delete() {
        return new PDODelete(static::TABLE, static::getDB());
    }

    /**
     * Loads a model based on a primary key column value
     * @param $id String the primary key value to search for
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     * @throws \Exception if the model does not contain primary keys
     */
    public static function loadByPrimaryKey($id, $throwIfNotFound=true) {
        if(!static::PRIMARY)
            throw new \Exception("Constant 'PRIMARY' is not set. Cannot load " . static::modelName() . " Model");
        if(static::CACHE_ENABLED
            && $Model = static::$mCache->fetch(get_called_class() . ':id:' . $id))
            return $Model;
        $Model = static::search()
            ->where(static::PRIMARY, $id)
            ->fetch();
        if(!$Model) {
            if($throwIfNotFound)
                throw new ModelNotFoundException(static::modelName() . " '{$id}' was not found");
            return NULL;
        }
        if(static::CACHE_ENABLED)
            static::$mCache->store(get_called_class() . ':id:' . $id, $Model);
        return $Model;
    }

    /**
     * Loads a model based on a search
     * @param String $search the column value to search for
     * @param mixed $columns a string list (comma delimited) or array of columns to search for.
     * Default is static::SEARCH or columns with PDOColumn::FlagSearch set
     * @param string $logic 'OR' or 'AND' logic between columns
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    public static function loadByColumns($search, $columns=NULL, $logic='OR', $throwIfNotFound=true) {
        $Model = static::searchByColumns($search, $columns, 1, $logic)
            ->fetch();
        if(!$Model && $throwIfNotFound)
            throw new ModelNotFoundException(static::modelName() . " was not found");
        return $Model;
    }

    /**
     * Creates a PDOModelSelect for searching models.
     * @return PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function search() {
        return new PDOModelSelect(static::getDB(), get_called_class());
    }

    /**
     * Searches for Models based on specified columns and values.
     * @param String $search the column value to search for
     * @param mixed $columns a string list (comma delimited) or array of columns to search for.
     * Default is static::SEARCH or columns with PDOColumn::FlagSearch set
     * @param int $limit the number of rows to return. Default is 1
     * @param string $logic 'OR' or 'AND' logic between columns. Default is 'OR'
     * @param string|NULL $compare set WHERE logic for each column [=, >, LIKE, etc]. Default is '='
     * @return PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if no columns were found
     */
    public static function searchByColumns($search, $columns=NULL, $limit=1, $logic='OR', $compare=NULL) {
        if($columns instanceof IArrayObject)
            $columns =$columns->getDataPath();
        $columns = static::findColumns($columns ?: static::SEARCH ?: PDOColumn::FlagSearch);
        if(!$columns)
            throw new \Exception("No SEARCH fields defined in ".static::modelName());

        $Select = static::search();

        $i = 0;
        foreach($columns as $name=>$Column) {
            if(strcasecmp($logic, 'OR')===0 && $i++) $Select->where('OR');
            if($compare) $name .= " {$compare} " ;
            $Select->where($name, $search);
        }

        $Select->limit($limit);
        return $Select;
    }


    /**
     * Delete a model entry by PRIMARY Key Column
     * @param $id mixed the PRIMARY Key to search for
     * @throws \Exception
     */
    public static function removeByPrimary($id) {
        if(!static::PRIMARY)
            throw new \Exception("Constant 'PRIMARY' is not set. Cannot Delete " . static::modelName());
        if($id === NULL)
            throw new \InvalidArgumentException("Remove ID can not be NULL. Cannot Delete " . static::modelName());
        $c = static::delete()
            ->where(static::PRIMARY, $id)
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete ".static::modelName()." '{$id}'");
        if(static::CACHE_ENABLED)
            static::$mCache->remove(get_called_class() . ':id:' . $id);
    }

    /**
     * Remove the row associated with a model from the database
     * @param PDOModel $Model the model to remove
     * @throws \Exception if no primary key is identified for this model
     */
    static function removeModel(PDOModel $Model) {
        if(!static::PRIMARY)
            throw new \Exception("Constant 'PRIMARY' is not set. Cannot Delete " . static::modelName());
        static::removeByPrimary($Model->{static::PRIMARY});
        Log::u(get_called_class(), "Deleted " . $Model);
    }
//
//    /**
//     * Override this to limit all default API 'GET', 'GET search', 'PATCH', and 'DELETE' calls by limiting the queries
//     * @param PDOWhere $Select the statement to limit.
//     * @param IRequest $Request The api request
//     * @return void
//     */
//    protected static function processAPI(PDOWhere $Select, IRequest $Request) { }
//
//    /**
//     * Override this to limit all default API 'GET' calls
//     * Modify $Request to update the request (i.e. unset($Request['id']); $Request['column'] = 'value';)
//     * @param PDOWhere $Select the statement to limit.
//     * @param IRequest $Request The api request to process and validate
//     * @return void
//     */
//    protected static function processAPIGet(PDOWhere $Select, IRequest $Request) { static::processAPI($Select, $Request); }
//
//    /**
//     * Override this to limit all default API 'GET search' calls
//     * @param PDOWhere $Select the statement to limit.
//     * @param IRequest $Request The api request to process and validate
//     * @return void
//     */
//    protected static function processAPISearch(PDOWhere $Select, IRequest $Request) { static::processAPIGet($Select, $Request); }
//
//    /**
//     * Override this to change or validate the request all default API 'POST' calls
//     * @param IRequest $Request The api request to process and validate
//     * @return void
//     */
//    protected static function processAPIPost(IRequest $Request) { }
//
//    /**
//     * Override this to limit all default API 'PATCH' calls
//     * @param PDOWhere $Select the statement to limit.
//     * @param IRequest $Request The api request to process and validate
//     * @return void
//     */
//    protected static function processAPIPatch(PDOWhere $Select, IRequest $Request) { static::processAPIGet($Select, $Request); }
//
//    /**
//     * Override this to limit all default API 'DELETE' calls
//     * @param PDOWhere $Select the statement to limit.
//     * @param IRequest $Request The api request to process and validate
//     * @return void
//     */
//    protected static function processAPIDelete(PDOWhere $Select, IRequest $Request) { static::processAPI($Select, $Request); }

    /**
     * Override this to limit all default API calls for this class
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @return void
     */
    static function limitAPIQuery(PDOWhere $Select, IRequest $Request) { }

    /**
     * Returns the model name from comment or the class name
     * @return string the model name
     */
    public static function modelName() {
        return static::MODEL_NAME ?: basename(get_called_class());
    }

    /**
     * Formats an underscored_column into TitleCase
     * @param String $column the column to format
     * @param bool $noSpace if true, all spaces are removed
     * @return mixed|string
     */
    protected static function toTitleCase($column, $noSpace=false) {
        $column = ucwords(str_replace('_', ' ', $column));
        if(!$noSpace)
            return $column;
        return str_replace(' ', '', $column);
    }

    /**
     * Return an instance of the class for building purposes
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance() {
        $R = new \ReflectionClass(get_called_class());
        if(!$R->isAbstract())
            return new static;
        return null;
    }
}
PDOModel::init();

/**
 * Custom select object returns PDOModel instances instead of arrays
 * Class PDOModelSelect the class to instantiate
 * @package CPath\Model\DB
 */
class PDOModelSelect extends PDOSelect {
    private $mClass;

    /**
     * Create a new PDOModelSelect
     * @param \PDO $DB the database instance
     * @param String|PDOModel $class The class name or instance
     */
    public function __construct(\PDO $DB, $class) {
        $table = $class::TABLE;
        parent::__construct($table, $DB, array($table . '.*'));
        $this->mClass = is_string($class) ? $class : get_class($class);
    }

    /**
     * Execute this query
     * @return $this|\PDOStatement
     */
    public function exec() {
        parent::exec();
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->mClass);
        return $this;
    }

    /**
     * @return PDOModel
     */
    public function fetch() {
        return parent::fetch();
    }
}
