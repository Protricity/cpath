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
use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIParam;
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIRequiredParam;
use CPath\Handlers\HandlerSet;
use CPath\Handlers\SimpleAPI;
use CPath\Interfaces\IArrayObject;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IRoutable;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IRouteBuilder;
use CPath\Interfaces\IXML;
use CPath\Log;
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

abstract class PDOModel implements IResponseAggregate, IGetDB, IJSON, IXML, IHandlerAggregate, IRoutable {
    //const Build_Ignore = true;
    //const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST

    const TableName = null;
    const ModelName = null;
    const Primary = null;
    const Validations = null;

    const SearchLimitMax = 100;
    const SearchLimit = 25;
    const SearchWildCard = false;   // true or false

    const CacheEnabled = false;
    const CacheTTL = 300;

    const Search = NULL;    // 'None|Index|SIndex|Primary|Exclude:[column1,column2]|[Include:][column1,column2]';
    const Insert = NULL;    // 'None|Index|SIndex|Primary|Exclude:[column1,column2]|[Include:][column1,column2]';
    const Update = NULL;    // 'None|Index|SIndex|Primary|Exclude:[column1,column2]|[Include:][column1,column2]';
    const Export = NULL;    // 'None|Index|SIndex|Primary|Exclude:[column1,column2]|[Include:][column1,column2]';

    const ColumnIsNumeric =  0x0001;
    const ColumnIsEnum =     0x0002;
    const ColumnIsNull =     0x0004;

    const ColumnIsIndex =    0x0010;
    const ColumnIsUnique =   0x0020;
    const ColumnIsPrimary =  0x0040;
    const ColumnIsAutoInc =  0x0080;

    const ColumnIsRequired = 0x0100;

    const ColumnIsInsert =   0x1000;
    const ColumnIsUpdate =   0x2000;
    const ColumnIsSearch =   0x4000;
    const ColumnIsExport =   0x8000;

    const DefaultFilter =   FILTER_SANITIZE_SPECIAL_CHARS;

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
     * Update a column value for this Model
     * @param String $column the column name to update
     * @param String $value the value to set
     * @param bool $commit set true to commit now, otherwise use ->commitColumns
     * @return $this
     */
    protected function updateColumn($column, $value, $commit=true) {
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
     * Update column values for this Model
     * @return int the number of columns updated
     * @throws \Exception if no primary key exists
     */
    protected function commitColumns() {
        if(!($primary = static::Primary))
            throw new \Exception("Constant 'Primary' is not set. Cannot Update table");
        $id = $this->$primary;
        if(!$this->mCommit) {
            Log::u(get_called_class(), "No Fields Updated for ".static::getModelName()." '{$id}'");
            return 0;
        }
        $set = '';
        $DB = static::getDB();
        foreach($this->mCommit as $column=>$value)
            $set .= ($set ? ",\n\t" : '') . "{$column} = ".$DB->quote($value);
        $SQL = "UPDATE ".static::TableName
            ."\n SET {$set}"
            ."\n WHERE ".static::Primary." = ".$DB->quote($id);
        $DB->exec($SQL);
        Log::u(get_called_class(), "Updated ".static::getModelName()." '{$id}'");
        $c = sizeof($this->mCommit);
        $this->mCommit = array();
        if(static::CacheEnabled)
            static::$mCache->store(get_called_class() . ':id:' . $id, $this, static::CacheTTL);
        return $c;
    }


    function toXML(\SimpleXMLElement $xml){
        foreach($this->getExportData() as $key=>$val)
            $xml->addAttribute($key, $val);

    }

    function toJSON(Array &$JSON){
        foreach($this->getExportData() as $key=>$val)
            $JSON[$key] = $val;
    }

    /**
     * Returns an associative array of columns and values for this object filtered by the tokens in constant Export.
     * Defaults to just primary key, if exists.
     * Modify const Export to change what data gets exported
     * @return Array
     */
    public function getExportData()
    {
        $export = array();
        foreach(static::findColumns(static::Export ?: static::ColumnIsExport) as $column => $data)
            $export[$column] = $this->$column;
        return $export;
    }

    /**
     * @return IResponse
     */
    public function getResponse() {
        return new Response("Retrieved '" . $this . "'", true, $this);
    }

    public function __toString() {
        if(static::Primary)
            return static::getModelName() . " '" . $this->{static::Primary} . "'";
        return static::getModelName();
    }

    // Implement IRoutable

    /**
     * Returns an array of all routes for this class
     * @param IRouteBuilder $Builder the IRouteBuilder instance
     * @return IRoute[]
     */
    function getAllRoutes(IRouteBuilder $Builder) {
        return $this->getAggregateHandler()->getAllRoutes($Builder);
    }

    // Implement IHandlerAggregate

    /**
     * Returns an IHandlerSet instance to represent this class
     * @return HandlerSet a set of common api routes for this model
     * @throws ValidationException
     * @throws ModelNotFoundException if no Model was found
     */
    function getAggregateHandler() {
        $Handlers = new HandlerSet($this);


        $Handlers->addHandlerByClass('POST', 'CPath\Model\DB\API_Post');




        $Source = $this;
        if(static::Primary) {
            $Handlers->addHandler('GET', new SimpleAPI(function(API $API, IRequest $Request) use ($Source) {
                $API->processRequest($Request);
                $Search = $Source::search();
                $Search->where($Source::Primary, $Request['id']);
                $Source::processAPIGet($Search, $Request);
                $data = $Search->fetch();
                if(!$data)
                    throw new ModelNotFoundException($Source::getModelName() . " '{$Request['id']}' was not found");
                return $data;
            }, array(
            ), "Get information about this ".$this->getModelName()));
        }

        $Handlers->addHandler('GET search', new SimpleAPI(function(API $API, IRequest $Request) use ($Source) {
            $API->processRequest($Request);
            $limit = $Request['limit'];
            if($limit < 1 || $limit > $Source::SearchLimitMax)
                $limit = $Source::SearchLimit;
            $search = $Request['search'];
            if($Source::SearchWildCard) {
                if(strpos($search, '*') !== false)
                    $search = str_replace('*', '%', $search);
                else
                    $search .= '%';
            }

            $Search = $Source::searchBySearchColumns($search, $limit, $Source::SearchWildCard ? 'LIKE' : '', $Request['search_by']);
            $Source::processAPISearch($Search, $Request);
            $data = $Search->fetchAll();
            return new Response("Found (".sizeof($data).") ".$Source::getModelName()."(s)", true, $data);
        }, array(
            'search' => new APIRequiredParam("Search for ".$Source::getModelName()),
            'search_by' => new APIParam("Search by column. Allowed: [".static::Search."]"),
            'limit' => new APIField("The Number of rows to return. Max=".static::SearchLimitMax),
        ), "Search for a ".$this->getModelName() ));

        if(static::Primary) {
            $columns = array();
            $columns[static::Primary] = new APIRequiredParam("ID of the " . $Source->getModelName() . " to be updated");
            foreach(static::findColumns(static::Update ?: static::ColumnIsUpdate) as $column=>$data)
                $columns[$column] = new APIField("Update " . $Source->getModelName() . " " . $data->getComment());
            if(sizeof($columns) > 1) {
                $Handlers->addHandler('PATCH', new SimpleAPI(function(API $API, IRequest $Request) use ($Source) {
                    $API->processRequest($Request);
                    $id = $Request->pluck($Source::Primary);

                    $Search = $Source::search();
                    $Search->where($Source::Primary, $id);
                    $Source::processAPIPatch($Search, $Request);
                    /** @var PDOModel $Model */
                    $Model = $Search->fetch();
                    if(!$Model)
                        throw new ModelNotFoundException($Source::getModelName() . " '{$id}' was not found");

                    $Source::validateRequest($Request);
                    foreach($Request as $column => $value)
                        $Model->updateColumn($column, $value, false);

                    $c = $Model->commitColumns();
                    if(!$c)
                        return new Response("No columns were updated for " . $Source::getModelName()." '{$id}'.", true, $Model);
                    return new Response("Updated {$c} Field(s) for " . $Source::getModelName()." '{$id}'.", true, $Model);

                }, $columns, "Update a ".$this->getModelName()));
            }


            $Handlers->addHandler('DELETE', new SimpleAPI(function(API $API, IRequest $Request) use ($Source) {
                $API->processRequest($Request);
                $Search = $Source::search();
                $Search->where($Source::Primary, $Request['id']);
                $Source::processAPIDelete($Search, $Request);
                $Model = $Search->fetch();
                if(!$Model)
                    throw new ModelNotFoundException($Source::getModelName() . " '{$Request['id']}' was not found");
                $Source::removeModel($Model);
                return new Response("Removed ".$Source::getModelName()."(s)", true, $Model);

            }, array(
                'id' => new APIRequiredParam($Source->getModelName() . " " . static::getColumn(static::Primary)->getComment()),
            ), "Delete a ".$this->getModelName()));
        }

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
    static function getAllColumns() { return array(); }

    /**
     * Return information for a column
     * @param String $name the name of the column
     * @return PDOColumn
     * @throws ColumnNotFoundException if the column was not found
     */
    static function getColumn($name) {
        $cols = static::getAllColumns();
        if(!isset($cols[$name]))
            throw new ColumnNotFoundException("Column '{$name}' could not be found in " . static::getModelName());
        return $cols[$name];
    }

    /**
     * Returns an indexed array of column names for this object filtered by the tokens in constant Export.
     * @param $tokens String|int the token or filter to use, or NULL for all columns
     * @return PDOColumn[] associative array of $columns and config data
     */
    static function findColumns($tokens) {
        if(is_int($tokens)) {
            $list = array();
            foreach(static::getAllColumns() as $col => $data)
                if($data->isFlag($tokens))
                    $list[$col] = $data;
            return $list;
        }
        $tokens = explode(':', $tokens);
        switch($tokens[0]) {
            case 'None':
                return array();
            case 'Index':
                return static::findColumns(self::ColumnIsIndex);
            case 'SIndex':
                $list = array();
                foreach(static::getAllColumns() as $col => $data)
                    if($data->isFlag(self::ColumnIsIndex) && !$data->isFlag(self::ColumnIsNumeric))
                        $list[$col] = $data;
                return $list;
            case 'Primary':
                return static::findColumns(self::ColumnIsPrimary);
            case 'Exclude':
                if(empty($tokens[1]) || !($tokens = explode(',', $tokens[1])))
                    return array();
                return array_diff_key(static::getAllColumns(), array_flip($tokens));
            case 'Include':
                if(empty($tokens[1]) || !($tokens = explode(',', $tokens[1])))
                    return array();
                return array_intersect_key(static::getAllColumns(), array_flip($tokens));
            default:
                if(!$tokens[0] || !($tokens = explode(',', $tokens[0])))
                    return array();
                return array_intersect_key(static::getAllColumns(), array_flip($tokens));
        }
    }

    /**
     * Validate a request of column values using compiled configuration
     * @param IRequest $Request the IRequest instance to validate
     * @throws ValidationException
     */
    static function validateRequest(IRequest $Request) {
        foreach($Request as $column=>$value)
            static::getColumn($column)
                ->validate($value);
    }

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

            if(!static::Primary) {
                static::insert(array_keys($row))
                    ->values(array_values($row));
                return NULL;
            } else {
                if(isset($row[static::Primary]))
                    $id = $row[static::Primary];
                $Insert = static::insert(array_keys($row))
                    ->requestInsertID(static::Primary)
                    ->values(array_values($row));
                if(!isset($id))
                    $id = $Insert->getInsertID();
                Log::u(get_called_class(), "Created ".static::getModelName()." '{$id}'");
                return static::loadByPrimaryKey($id);
            }
        } catch (\PDOException $ex) {
            if(stripos($ex->getMessage(), 'Duplicate')!==false) {
                $err = "A Duplicate ".static::getModelName()." already exists";
                if(Base::isDebug())
                    $err .= ': ' . $ex->getMessage();
                Log::u(get_called_class(), "Duplicate ".static::getModelName()." already exists");
                throw new ModelAlreadyExistsException($err, $ex->getCode(), $ex);
            }
            throw $ex;
        }
    }

    // Database methods

    /**
     * Create a PDOSelect object for this table
     * @param $_selectArgs array|mixed an array or series of varargs of columns to select
     * @return PDOSelect
     */
    static function select($_selectArgs) {
        $args = is_array($_selectArgs) ? $_selectArgs : func_get_args();
        return new PDOSelect(static::TableName, static::getDB(), $args);
    }

    /**
     * Create a PDOInsert object for this table
     * @param $_insertArgs array|mixed an array or series of varargs of columns to insert
     * @return PDOInsert
     */
    static function insert($_insertArgs) {
        $DB = static::getDB();
        $args = is_array($_insertArgs) ? $_insertArgs : func_get_args();
        return $DB->insert(static::TableName, $args);
    }

    /**
     * Create a PDOUpdate object for this table
     * @param $_columnArgs array|mixed an array or series of varargs of columns to be updated
     * @return PDOUpdate
     */
    static function update($_columnArgs) {
        $args = is_array($_columnArgs) ? $_columnArgs : func_get_args();
        return new PDOUpdate(static::TableName, static::getDB(), $args);
    }

    /**
     * Create a PDODelete object for this table
     * @return PDODelete
     */
    static function delete() {
        return new PDODelete(static::TableName, static::getDB());
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
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot load " . static::getModelName() . " Model");
        if(static::CacheEnabled
            && $Model = static::$mCache->fetch(get_called_class() . ':id:' . $id))
            return $Model;
        $Model = static::search()
            ->where(static::Primary, $id)
            ->fetch();
        if(!$Model) {
            if($throwIfNotFound)
                throw new ModelNotFoundException(static::getModelName() . " '{$id}' was not found");
            return NULL;
        }
        if(static::CacheEnabled)
            static::$mCache->store(get_called_class() . ':id:' . $id, $Model);
        return $Model;
    }

    /**
     * Loads a model based on a search
     * @param array|mixed $columns an array of key-value pairs to search for
     * @param string $logic 'OR' or 'AND' logic between columns
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    public static function loadByColumns($columns, $logic='OR', $throwIfNotFound=true) {
        $Model = static::searchByColumns($columns, 1, $logic)
            ->fetch();
        if(!$Model && $throwIfNotFound)
            throw new ModelNotFoundException(static::getModelName() . " was not found");
        return $Model;
    }

    /**
     * Loads a model based on a search column
     * @param $columnName String the database column to search for
     * @param $value String the column value to search for
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    public static function loadByColumn($columnName, $value, $throwIfNotFound=true) {
        return static::loadByColumns(array($columnName => $value), NULL, $throwIfNotFound);
    }

    /**
     * Loads a Model using all indexed columns.
     * @param mixed $search a value to search for
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    public static function loadByAnyIndex($search, $throwIfNotFound=true) {
        $Model = static::searchBySearchColumns($search)
            ->fetch();
        if(!$Model && $throwIfNotFound)
            throw new ModelNotFoundException(static::getModelName() . " '{$search}' was not found");
        return $Model;
    }

    /**
     * Creates a PDOSelect for searching models.
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function search() {
        return new PDOSelectObject(static::TableName, static::getDB(), get_called_class());
    }

    /**
     * @param $columnName String the database column to search for
     * @param $value String the column value to search for
     * @param int $limit the number of rows to return
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function searchByColumn($columnName, $value, $limit=1) {
        return static::searchByColumns(array($columnName => $value), $limit);
    }

    /**
     * Searches for Models based on specified columns and values.
     * @param array $columns an array of key-value pairs to search for
     * @param int $limit the number of rows to return
     * @param string $logic 'OR' or 'AND' logic between columns
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function searchByColumns($columns, $limit=1, $logic='OR') {
        if($columns instanceof IArrayObject)
            $columns =$columns->getDataPath();

        $Select = static::search();

        $i = 0;
        foreach($columns as $k=>$v)
            if($v!==null) {
                if($logic=='OR' && $i++) $Select->where('OR');
                $Select->where($k, $v);
            }

        $Select->limit($limit);
        return $Select;
    }


    /**
     * Searches for Models using all search columns.
     * @param mixed $search a value to search for
     * @param int $limit the number of rows to return
     * @param String $compare custom comparison (ex. '<', 'LIKE', '=func(?)')
     * @param String $column limit search to a specific column
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if the model does not contain index keys
     * @throws ValidationException if the specified $column does not exist in the search list
     */
    public static function searchBySearchColumns($search, $limit=1, $compare=NULL, $column=NULL) {
        $columns = static::findColumns(static::Search ?: self::ColumnIsSearch);
        if(!$columns)
            throw new \Exception("No Indexes defined in ".static::getModelName());

        if($column && empty($columns[$column]))
                throw new ValidationException("Invalid 'search_by'. Allowed: [".implode(', ', array_keys($columns))."]");

        $Select = static::search();

        $i = 0;
        foreach($columns as $column => $data){
            if($i++) $Select->where('OR');
            if($compare) $column .= ' ' . $compare;
            $Select->where($column, $search);
        }

        $Select->limit($limit);
        return $Select;
    }

    /**
     * Delete a model entry by Primary Key Column
     * @param $id mixed the Primary Key to search for
     * @throws \Exception
     */
    public static function removeByPrimary($id) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . static::getModelName());
        $c = static::delete()
            ->where(static::Primary, $id)
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete ".static::getModelName()." '{$id}'");
        Log::u(get_called_class(), "Deleted ".static::getModelName()." '{$id}'");
        if(static::CacheEnabled)
            static::$mCache->remove(get_called_class() . ':id:' . $id);
    }

    /**
     * Remove the row associated with a model from the database
     * @param PDOModel $Model the model to remove
     * @throws \Exception if no primary key is identified for this model
     */
    protected static function removeModel(PDOModel $Model) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . static::getModelName());
        static::removeByPrimary($Model->{static::Primary});
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
     * Returns the model name from comment or the class name
     * @return string the model name
     */
    public static function getModelName() {
        return static::ModelName ?: basename(get_called_class());
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
    static function getBuildableInstance() {
        return new static;
    }
}
PDOModel::init();

/**
 * Custom select object returns PDOModel instances instead of arrays
 * Class PDOSelectObject the class to instantiate
 * @package CPath\Model\DB
 */
class PDOSelectObject extends PDOSelect {
    private $mClass;

    /**
     * Create a new PDOSelectObject
     * @param String $table the table to perform the query on
     * @param \PDO $DB the database instance
     * @param String $Class The class to instantiate
     */
    public function __construct($table, \PDO $DB, $Class) {
        parent::__construct($table, $DB, array($table . '.*'));
        $this->mClass = $Class;
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
}
