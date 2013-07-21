<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIParam;
use CPath\Handlers\APISet;
use CPath\Handlers\SimpleAPI;
use CPath\Handlers\ValidationException;
use CPath\Interfaces\IHandler;
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
    const BUILD_IGNORE = true;
    const ROUTE_METHODS = 'GET|POST|CLI';     // Default accepted methods are GET and POST

    const TableName = null;
    const Primary = null;
    Const Columns = null;
    Const Types = null;
    const SearchKeys = null;
    const SearchTypes = null;

    const SearchLimitMax = 100;
    const SearchLimit = 25;

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
        if(static::Primary)
            return $this->getDataInclude(static::Primary);
        return array();
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
                $id = static::insert(array_keys($row))
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
            if(stripos($ex->getMessage(), 'Duplicate')!==false)
                throw new ModelAlreadyExistsException($ex->getMessage(), $ex->getCode(), $ex);
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
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if the model does not contain index keys
     */
     public static function searchByAnyIndex($any, $limit=1) {
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
            throw new \Exception("Unable to delete User '{$id}'");
    }


    protected static function removeModel(PDOModel $Model) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . static::getModelName());
        static::removeByPrimary($Model->mRow[static::Primary]);
    }

    /**
     * @return APISet a set of general api handlers for this model.
     * @throws ValidationException
     */
    public static function getAPISet()
    {
        /** @var PDOModel $Class */
        $Class = get_called_class();
        $APISet = new APISet($Class);
        if(static::Primary) {
            $APISet->addAPI('get', new SimpleAPI(function(API $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                return $Class::loadByPrimaryKey($request['id']);
            }, array(
                'id' => new APIParam($Class." ID"),
            )));
        }

        if(static::SearchKeys) {
            $APISet->addAPI('search', new SimpleAPI(function(API $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                $limit = $request['limit'];
                if($limit < 1 || $limit > static::SearchLimitMax)
                    $limit = static::SearchLimit;
                if($by = ($request['searchby'])) {
                    $keys = explode(',', $Class::SearchKeys);
                    if(!in_array($by, $keys))
                        throw new ValidationException("Invalid 'searchby'. Allowed: [".$Class::SearchKeys."]");
                    $Search = $Class::searchByField($by, $request['search'], $limit)->fetchAll();
                }
                else
                    $Search = $Class::searchByAnyIndex($request['search'], $limit)->fetchAll();
                return new Response("Found (".sizeof($Search).") ".$Class."(s)", true, $Search);
            }, array(
                'search' => new APIParam("Search for {$Class}"),
                'searchby' => new APIField("Search by field. Allowed: [".static::SearchKeys."]"),
                'limit' => new APIField("The Number of fields to return. Max=".static::SearchLimitMax),
            )));
        }

        return $APISet;
    }

    /**
     * @return IHandler $Handler
     */
    static function getHandler() {
        return static::getAPISet();
    }

    public static function getModelName() {
        return basename(get_called_class());
    }
}
