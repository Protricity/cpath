<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;

use CPath\Config;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Handlers\HandlerSet;
use CPath\Interfaces\IArrayObject;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IXML;
use CPath\Log;
use CPath\Model\ExceptionResponse;
use CPath\Model\Response;
use CPath\Util;

interface IGetDB {
    /**
     * @return PDODatabase
     */
    static function getDB();
}

class ModelNotFoundException extends \Exception {}
class ColumnNotFoundException extends \Exception {}
class InvalidPermissionException extends \Exception {}
class ModelAlreadyExistsException extends \Exception implements IResponseAggregate {

    /**
     * @return IResponse
     */
    function createResponse() {
        $Response = new ExceptionResponse($this);
        $Response->setStatusCode(IResponse::STATUS_CONFLICT);
        return $Response;
    }
}


abstract class PDOModel implements IResponseAggregate, IGetDB, IJSON, IXML, IBuildable {
    //const BUILD_IGNORE = true;
    //const ROUTE_METHODS = 'GET,POST,CLI';     // Default accepted methods are GET and POST

    const TABLE = null;
    const MODEL_NAME = null;

    const SEARCH_LIMIT_MAX = 100;
    const SEARCH_LIMIT = 25;
    const SEARCH_WILDCARD = false;   // true or false

    const HANDLER_IDS = NULL;   // Identifier column or list of columns for such endpoints as GET, PATCH, DELETE
    const SEARCH = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';
    const INSERT = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';
    const UPDATE = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';
    const EXPORT = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';
    const EXPORT_SEARCH = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';

    const DEFAULT_FILTER =   FILTER_SANITIZE_SPECIAL_CHARS;

    // Auto generate short names for all fields (for use in CLI)
    const AUTO_SHORTS = false;

    const SECURITY_DISABLED = false;


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


    function toXML(\SimpleXMLElement $xml){
        foreach($this->exportData() as $key=>$val)
            if(is_scalar($val) || $val === null)
                $xml->addAttribute($key, $val);
            else {
                $xml2 = $xml->addChild($key);
                Util::toXML($val, $xml2);
            }
    }

    function toJSON(Array &$JSON){
        foreach($this->exportData() as $key=>$val)
            $JSON[$key] = Util::toJSON($val);
    }

    /**
     * Returns an associative array of columns and values for this object filtered by the tokens in constant EXPORT.
     * Defaults to just primary key, if exists.
     * Modify const EXPORT to change what data gets exported by default
     * @param mixed|NULL $columns array or list (comma delimited) of columns to export
     * @return Array
     */
    public function exportData($columns=NULL)
    {
        $export = array();
        foreach(static::findColumns($columns ?: static::EXPORT ?: PDOColumn::FLAG_EXPORT) as $column => $data)
            $export[$column] = $this->$column;
        return $export;
    }

    /**
     * @return IResponseAggregate
     */
    public function createResponse() {
        return new Response("Retrieved " . $this, true, $this);
    }

    /**
     * Returns the default IHandlerSet collection for this PDOModel type.
     * @param HandlerSet $Handlers a set of handlers to add to, otherwise a new HandlerSet is created
     * @return HandlerSet a set of common handler routes for this PDOModel type
     */
    function loadDefaultHandlers(HandlerSet $Handlers=NULL) {
        if($Handlers === NULL)
            $Handlers = new HandlerSet($this);

        $Handlers->add('GET search', new API_GetSearch($this));
        $Handlers->add('POST', new API_Post($this));

        return $Handlers;
    }


    public function __toString() {
        if($id = static::HANDLER_IDS)
            return static::modelName() . " '" . $this->$id . "'";
        return static::modelName();
    }


    function __set($name, $value) {
        throw new \InvalidArgumentException("May not set undefined properties to ".self::modelName());
    }

    // Statics

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
    final static function loadColumn($name) {
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
    final static function findColumns($tokens) {
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
                        return static::findColumns(PDOColumn::FLAG_INDEX);
                    case ':sindex':
                        $list = array();
                        foreach(static::loadAllColumns() as $col => $data)
                            if($data->isFlag(PDOColumn::FLAG_INDEX) && !$data->isFlag(PDOColumn::FLAG_NUMERIC))
                                $list[$col] = $data;
                        return $list;
                    case ':primary':
                        return static::findColumns(PDOColumn::FLAG_PRIMARY);
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
        $cols = static::loadAllColumns();
        $ret = array();
        foreach($tokens as $token)
            if(isset($cols[$token])) {
                $ret[$token] = $cols[$token];
            } else {
                throw new \Exception("Column '{$token}' not found in " . self::modelName());
            }
        return $ret;
        //return array_intersect_key($cols, array_flip($tokens));
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

    protected static function insertRow(Array $row) {
        static::insert(array_keys($row))
            ->values(array_values($row));
        Log::u(get_called_class(), "Created " . static::MODEL_NAME);
    }

    /**
     * Creates a new Model based on the provided row of column value pairs
     * @param array|mixed $row column value pairs to insert into new row
     * @return void
     * @throws ModelAlreadyExistsException
     * @throws \Exception|\PDOException
     * @throws ValidationException if a column fails to validate
     */
    final public static function createFromArray($row) {
        if($row instanceof IArrayObject)
            $row = $row->getDataPath();

        foreach(static::loadAllColumns() as $Column)
            if($Column->hasDefaultValue())
                if(!isset($row[$Column->getName()]))
                    $row[$Column->getName()] = $Column->getDefaultValue();

        foreach($row as $k=>$v)
            if($v===null)
                unset($row[$k]);
        try {
//            foreach($row as $column => $value) // No re-validation
//                static::validateColumn($column, $value);
            static::insertRow($row);
            return;
        } catch (\PDOException $ex) {
            if(stripos($ex->getMessage(), 'Duplicate')!==false) {
                $err = "A Duplicate ".static::modelName()." already exists";
                if(Config::$Debug)
                    $err .= ': ' . $ex->getMessage();
                Log::u(get_called_class(), "Duplicate ".static::modelName()." already exists");
                throw new ModelAlreadyExistsException($err, $ex->getCode(), $ex);
            }
            throw $ex;
        }
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a model instance
     * Note: This model instance is NOT pulled from the database, but is instead filled with the row values.
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOModel the new instance with filled values
     * @throws ModelAlreadyExistsException
     * @throws ValidationException if a column fails to validate
     */
    final public static function createAndFill($row) {
        static::createFromArray($row);
        $Model = new static();
        foreach($row as $k=>$v)
            $Model->$k = $v;
        return $Model;
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a new instance
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOPrimaryKeyModel the created model instance
     * @throws ModelAlreadyExistsException
     * @throws ValidationException if a column fails to validate
     */
    final static function createOrFill($row) {
        $Model = static::search()
            ->whereAll($row)
            ->fetch();
        if($Model)
            return $Model;
        return static::createAndFill($row);
    }

    // Database methods

    /**
     * Create a PDOModelSelect object for this table
     * @param $_selectArgs array|mixed an array or series of varargs of columns to select
     * @return PDOSelect
     */
    final static function select($_selectArgs=NULL) {
        $args = func_num_args() > 1 ? func_get_args() : (Array)$_selectArgs;
        return new PDOSelect(static::TABLE, static::getDB(), $args);
    }

    /**
     * Applies a search to PDOSelect based on specified columns and values.
     * @param String|int|Array|PDOSelect $Select the columns to return or the PDOSelect instance to search in
     * @param String|Array $search the column value to search for or an associative array of column/value pairs to search for.
     * Note: If an array is passed here, the $columns value is ignored.
     * @param mixed $columns a string list (comma delimited) or array of columns to search for.
     * Default is static::SEARCH or columns with PDOColumn::FLAG_SEARCH set
     * @param int $limit the number of rows to return. Default is 1
     * @param string $logic 'OR' or 'AND' logic between columns. Default is 'OR'
     * @param string|NULL $compare set WHERE logic for each column [=, >, LIKE, etc]. Default is '='
     * @return PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if no columns were found
     */
    final static function selectByColumns($Select, $search, $columns=NULL, $limit=1, $logic='OR', $compare=NULL) {
        if(!$Select instanceof PDOSelect)
            $Select = static::select($Select);

        if(strcasecmp($logic, 'OR')===0)
            $Select->setFlag(PDOWhere::LOGIC_OR);

        if(!is_array($search)) {

            if($columns instanceof IArrayObject)
                $columns = $columns->getDataPath();
            $columns = static::findColumns($columns ?: static::SEARCH ?: PDOColumn::FLAG_SEARCH);
            if(!$columns)
                throw new \Exception("No SEARCH fields defined in ".static::modelName());
            foreach($columns as $name=>$Column) {
                if($compare) $name .= " {$compare} " ;
                $Select->where($name, $search);
            }

        } else {
            foreach($search as $name => $value)
                $Select->where($name, $value);
        }

        $Select->limit($limit);
        return $Select;
    }

    /**
     * Create a PDOInsert object for this table
     * @param $_insertArgs array|mixed an array or series of varargs of columns to insert
     * @return PDOInsert
     */
    final static function insert($_insertArgs) {
        $DB = static::getDB();
        $args = func_num_args() > 1 ? func_get_args() : array_keys(static::findColumns($_insertArgs));
        return $DB->insert(static::TABLE, $args);
    }

    /**
     * Create a PDOUpdate object for this table
     * @param $_columnArgs array|mixed an array or series of varargs of columns to be updated
     * @return PDOUpdate
     */
    final static function update($_columnArgs) {
        $args = func_num_args() > 1 ? func_get_args() : array_keys(static::findColumns($_columnArgs));
        return new PDOUpdate(static::TABLE, static::getDB(), $args);
    }

    /**
     * Create a PDODelete object for this table
     * @return PDODelete
     */
    final static function delete() {
        return new PDODelete(static::TABLE, static::getDB());
    }

    /**
     * Loads a model based on a search
     * @param String|Array $search the column value to search for or an associative array of column/value pairs to search for.
     * Note: If an array is passed here, the $columns value is ignored.
     * @param mixed $columns a string list (comma delimited) or array of columns to search for.
     * Default is static::SEARCH or columns with PDOColumn::FLAG_SEARCH set
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @param string $logic 'OR' or 'AND' logic between columns
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    final public static function loadByColumns($search, $columns=NULL, $throwIfNotFound=true, $logic='OR') {
        $Model = static::searchByColumns($search, $columns, 1, $logic)
            ->fetch();
        if(!$Model && $throwIfNotFound) {
            throw new ModelNotFoundException(static::modelName() . " '{$search}' was not found");
        }
        return $Model;
    }

    /**
     * Creates a PDOModelSelect for searching models.
     * @return PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    final public static function search() {
        return new PDOModelSelect(static::getDB(), get_called_class());
    }

    /**
     * Searches for Models based on specified columns and values.
     * @param String|Array $search the column value to search for or an associative array of column/value pairs to search for.
     * Note: If an array is passed here, the $columns value is ignored.
     * @param mixed $columns a string list (comma delimited) or array of columns to search for.
     * Default is static::SEARCH or columns with PDOColumn::FLAG_SEARCH set
     * @param int $limit the number of rows to return. Default is 1
     * @param string $logic 'OR' or 'AND' logic between columns. Default is 'OR'
     * @param string|NULL $compare set WHERE logic for each column [=, >, LIKE, etc]. Default is '='
     * @return PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if no columns were found
     */
    final public static function searchByColumns($search, $columns=NULL, $limit=1, $logic='OR', $compare=NULL) {
        return static::selectByColumns(static::search(), $search, $columns, $limit, $logic, $compare);
    }

    /**
     * Returns the model name from comment or the class name
     * @return string the model name
     */
    public static function modelName() {
        return static::MODEL_NAME ?: basename(get_called_class());
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
//
//    public function select($field, $alias=NULL, $name=NULL) {
//        throw new \BadFunctionCallException("select() is disabled for PDOModelSelect");
//    }

    /**
     * Execute this query
     * @return $this|\PDOStatement
     */
    public function exec() {
        parent::exec();
        $this->mStmt->setFetchMode(\PDO::FETCH_CLASS, $this->mClass);
        return $this;
    }

    /**
     * @return PDOModel
     */
    public function fetch() {
        return parent::fetch();
    }
}
