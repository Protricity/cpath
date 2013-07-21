<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Base;
use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIParam;
use CPath\Handlers\APIRequiredParam;
use CPath\Handlers\APISet;
use CPath\Handlers\SimpleAPI;
use CPath\Handlers\ValidationException;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IXML;
use CPath\Model\Response;

interface IGetDB {
    /**
     * @return PDODatabase
     */
    static function getDB();
}

class ModelNotFoundException extends \Exception {}
class ModelAlreadyExistsException extends \Exception {}

abstract class PDOModel implements IResponseAggregate, IGetDB, IJSON, IXML, IHandlerAggregate {
    const Build_Ignore = true; // TODO: Title case
    const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST

    const TableName = null;
    const Primary = null;
    Const Columns = null;
    Const Types = null;
    const SearchKeys = null;
    const SearchTypes = null;

    const SearchLimitMax = 100;
    const SearchLimit = 25;
    const SearchAllowWildCard = false;   // true or false

    const ExportFields = 'Primary'; // 'All|None|Index|Primary|Exclude:[field1,field2]|Include:[field1,field2]';

    protected $mRow = null;
    private $mCommit = array();

    /**
     * PDOModel Constructor parameters must be optional. An 'empty' model must be created if no parameters are passed.
     * @param mixed|null $id if passed, the model is created with the specified id by Primary key, if exists.
     * @throws ModelNotFoundException if no model was found
     * @throws \Exception if the primary key is not set when $id is passed
     */
    public function __construct($id=NULL) {
        if($id !== NULL) {
            if(!static::Primary)
                throw new \Exception("Model '".get_class($this)."' has no primary key set");
            $row = static::search(true)
                ->where(static::Primary, $id)
                ->fetch();
            if(!$row)
                throw new ModelNotFoundException(get_class($this) . " '{$id}' not found");
            $this->setData($row);
        }
    }

    protected function setData(Array $row) {
        $this->mRow = $row;
        return $this;
    }

    public function setField($field, $value, $commit=true) {
        $this->mCommit[$field] = $value;
        if($commit) {
            if(!static::Primary)
                throw new \Exception("Constant 'Primary' is not set. Cannot Update table");
            $set = '';
            $DB = static::getDB();
            foreach($this->mCommit as $field=>$value)
                $set .= ($set ? ",\n\t" : '') . "{$field} = ".$DB->quote($value);
            $SQL = "UPDATE ".static::TableName
                ."\n SET {$set}"
                ."\n WHERE ".static::Primary." = ".$DB->quote($this->mRow[static::Primary]);
            $DB->exec($SQL);
            $this->mCommit = array();
        }
        $this->mRow[$field] = $value;
        return $this;
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
     * @return IResponse
     */
    public function getResponse() {
        return new Response("Retrieved '" . $this . "'", true, $this);
    }

    /**
     * Returns an IResponse for this object. Defaults to just primary key, if exists.
     * Overwrite this method and return $this->getDataInclude(...) or $this->getDataExclude(...) to return more data.
     * @return Array
     */
    public function getExportData()
    {
        if(!$f = static::ExportFields)
            return array();
        $f = explode(':', $f);
        switch(array_shift($f)) {
            case 'All':
                return $this->mRow;
            default:
            case 'None':
                return array();
            case 'Index':
                if(!static::SearchKeys)
                    return array();
                return $this->getDataInclude(explode(',', static::SearchKeys));
            case 'Primary':
                if(!static::Primary)
                    return array();
                return $this->getDataInclude(static::Primary);
            case 'Exclude':
                return $this->getDataExclude($f);
            case 'Include':
                return $this->getDataInclude($f);
        }
    }

    /**
     * @param String $_keyArgs an array or varargs of all the fields to exclude from the response.
     * If no fields are passed, the entire array is used.
     * @return Response
     */
    public function getDataExclude($_keyArgs) {
        $args = is_array($_keyArgs) ? $_keyArgs : func_get_args();
        $row = array();
        foreach($this->mRow as $key=>$value)
            if(!in_array($key, $args))
                $row[$key] = $value;
        return $row;
    }

    /**
     * @param String $_keyArgs an array or varargs of all the fields to return in the response.
     * @return Array
     */
    public function getDataInclude($_keyArgs) {
        $args = is_array($_keyArgs) ? $_keyArgs : func_get_args();
        $row = array();
        foreach($args as $name)
            if($name) $row[$name] = $this->mRow[$name];
        return $row;
    }

    public function __toString() {
        if(static::Primary)
            return get_class($this) . " '" . $this->mRow[static::Primary] . "'";
        return get_class($this);
    }


    // Statics

    /**
     * Creates a new Model based on the provided row of key value pairs
     * @param array $row key value pairs to insert into new row
     * @return PDOModel|null returns NULL if no primary key is available
     * @throws ModelAlreadyExistsException
     * @throws \Exception|\PDOException
     */
    public static function createFromArray(Array $row) {
       foreach($row as $k=>$v)
            if($v===null)
                unset($row[$k]);
        try {
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
                return static::loadByPrimaryKey($id);
            }
        } catch (\PDOException $ex) {
            if(stripos($ex->getMessage(), 'Duplicate')!==false) {
                $err = "A Duplicate ".static::getModelName()." already exists";
                if(Base::isDebug())
                    $err .= ': ' . $ex->getMessage();
                throw new ModelAlreadyExistsException($err, $ex->getCode(), $ex);
            }
            throw $ex;
        }
    }

    // Database methods

    /**
     * Create a PDOSelect object for this table
     * @param $_selectArgs array|mixed an array or series of varargs of fields to select
     * @return PDOSelect
     */
    static function select($_selectArgs) {
        $args = is_array($_selectArgs) ? $_selectArgs : func_get_args();
        return new PDOSelect(static::TableName, static::getDB(), $args);
    }

    /**
     * Create a PDOInsert object for this table
     * @param $_insertArgs array|mixed an array or series of varargs of fields to insert
     * @return PDOInsert
     */
    static function insert($_insertArgs) {
        $DB = static::getDB();
        $args = is_array($_insertArgs) ? $_insertArgs : func_get_args();
        return $DB->insert(static::TableName, $args);
    }

    /**
     * Create a PDOUpdate object for this table
     * @param $_fieldArgs array|mixed an array or series of varargs of fields to be updated
     * @return PDOUpdate
     */
    static function update($_fieldArgs) {
        $args = is_array($_fieldArgs) ? $_fieldArgs : func_get_args();
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
     * Creates a model instance from a fetched row
     * @param array $row the database row
     * @return PDOModel a new instance of the model
     */
    public static function fetchCallback(Array $row) {
        /** @var PDOModel $M */
        $M = new static();
        $M->setData($row);
        return $M;
    }

    /**
     * Loads a model based on a primary key column value
     * @param $search String the primary key value to search for
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     * @throws \Exception if the model does not contain primary keys
     */
    public static function loadByPrimaryKey($search) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot load " . static::getModelName() . " Model");
        $Model = static::search()
            ->where(static::Primary, $search)
            ->fetch();
        if(!$Model)
            throw new ModelNotFoundException(static::getModelName() . " '{$search}' was not found");
        return $Model;
    }

    /**
     * Creates a PDOSelect for searching models.
     * @param bool $asRow if true, returns an array of data instead of an instance of the model
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function search($asRow=false) {
        $alias = static::TableName . '.';
        $Select = static::select($alias . '*');
        if(!$asRow)
            $Select->setCallback(get_called_class().'::fetchCallback');
        return $Select;
    }

    /**
     * @param $fieldName String the database field to search for
     * @param $value String the field value to search for
     * @param int $limit the number of rows to return
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function searchByField($fieldName, $value, $limit=1) {
        return static::searchByFields(array($fieldName => $value), $limit);
    }

    /**
     * Searches for Models based on specified fields and values.
     * @param array $fields an array of key-value pairs to search for
     * @param int $limit the number of rows to return
     * @param string $logic 'OR' or 'AND' logic between fields
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function searchByFields(Array $fields, $limit=1, $logic='OR') {
        $Select = static::search();

        $i = 0;
        foreach($fields as $k=>$v)
            if($v!==null) {
                if($logic=='OR' && $i++) $Select->where('OR');
                $Select->where($k, $v);
            }

        $Select->limit($limit);
        return $Select;
    }


    /**
     * Searches for Models using all indexed fields.
     * @param mixed $any a value to search for
     * @param int $limit the number of rows to return
     * @param String $compare custom comparison (ex. '<', 'LIKE', '=func(?)')
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if the model does not contain index keys
     */
    public static function searchByAnyIndex($any, $limit=1, $compare=NULL) {
        if(!static::SearchKeys)
            throw new \Exception("No Indexes defined in ".static::getModelName());
         $Select = static::search();

        $i = 0;
        $keys = explode(',', static::SearchKeys);
        if(!is_numeric($any)) {
            $types = static::SearchTypes;
            $keys2 = array();
            foreach($keys as $j=>$key)
                if($types[$j] != 'i')
                    $keys2[] = $key;
            $keys = $keys2;
        }
        foreach($keys as $key){
            if($i++) $Select->where('OR');
            if($compare) $key .= ' ' . $compare;
            $Select->where($key, $any);
        }

        $Select->limit($limit);
        return $Select;
    }

    public static function removeByPrimary($id) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . static::getModelName());
        $c = static::delete()
            ->where(static::Primary, $id)
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete ".static::getModelName()." '{$id}'");
    }


    protected static function removeModel(PDOModel $Model) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . static::getModelName());
        static::removeByPrimary($Model->mRow[static::Primary]);
    }

    /**
     * @return APISet a set of general api handlers for this model.
     * @throws ValidationException
     * @throws ModelNotFoundException if no Model was found
     */
    public static function getAPISet()
    {
        /** @var PDOModel $Class */
        $Class = get_called_class();
        $APISet = new APISet($Class);
        if(static::Primary) {
            $APISet->addAPI('get', new SimpleAPI(function(API $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                $Search = $Class::search();
                $Search->where($Class::Primary, $request['id']);
                $Class::limitAPIGet($Search);
                $data = $Search->fetch();
                if(!$data)
                    throw new ModelNotFoundException($Class::getModelName() . " '{$request['id']}' was not found");
                return $data;
            }, array(
                'id' => new APIRequiredParam($Class." ID"),
            )));
        }

        if(static::SearchKeys) {
            $APISet->addAPI('search', new SimpleAPI(function(API $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                $limit = $request['limit'];
                if($limit < 1 || $limit > $Class::SearchLimitMax)
                    $limit = $Class::SearchLimit;
                $search = $request['search'];
                $wildCard = false;
                if(strpos($search, '*') !== false && $Class::SearchAllowWildCard) {
                    $search = str_replace('*', '%', $search);
                    $wildCard = true;
                }

                if($by = ($request['searchby'])) {
                    $keys = explode(',', $Class::SearchKeys);
                    if(!in_array($by, $keys))
                        throw new ValidationException("Invalid 'searchby'. Allowed: [".$Class::SearchKeys."]");

                    $Search = $Class::searchByField($wildCard ? $by . ' LIKE ' : $by, $search, $limit);
                }
                else
                    $Search = $Class::searchByAnyIndex($search, $limit, $wildCard ? 'LIKE' : '');
                $Class::limitAPISearch($Search);
                $data = $Search->fetchAll();
                return new Response("Found (".sizeof($data).") ".$Class::getModelName()."(s)", true, $data);
            }, array(
                'search' => new APIRequiredParam("Search for ".$Class::getModelName()),
                'searchby' => new APIParam("Search by field. Allowed: [".static::SearchKeys."]"),
                'limit' => new APIField("The Number of fields to return. Max=".static::SearchLimitMax),
            )));
        }

        if(static::Primary) {
            $APISet->addAPI('remove', new SimpleAPI(function(API $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                $Search = $Class::search();
                $Search->where($Class::Primary, $request['id']);
                $Class::limitAPIRemove($Search);
                $Model = $Search->fetch();
                if(!$Model)
                    throw new ModelNotFoundException($Class::getModelName() . " '{$request['id']}' was not found");
                $Class::removeModel($Model);
                return new Response("Removed ".$Class::getModelName()."(s)", true, $Model);

            }, array(
                'id' => new APIRequiredParam($Class." ID"),
            )));
        }

        return $APISet;
    }

    /**
     * Override this to limit all default API 'search', 'get', and 'remove' calls
     * @param PDOWhere $Select the statement to limit.
     */
    protected static function limitAPI(PDOWhere $Select) {}

    /**
     * Override this to limit all default API 'search' calls
     * @param PDOWhere $Select the statement to limit.
     */
    protected static function limitAPISearch(PDOWhere $Select) { static::limitAPI($Select); }

    /**
     * Override this to limit all default API 'get' calls
     * @param PDOWhere $Select the statement to limit.
     */
    protected static function limitAPIGet(PDOWhere $Select) { static::limitAPI($Select); }

    /**
     * Override this to limit all default API 'remove' calls
     * @param PDOWhere $Select the statement to limit.
     */
    protected static function limitAPIRemove(PDOWhere $Select) { static::limitAPI($Select); }

    /**
     * @return APISet a set of common api routes for this model
     */
    static function getHandler() {
        return static::getAPISet();
    }

    public static function getModelName() {
        return basename(get_called_class());
    }
}
