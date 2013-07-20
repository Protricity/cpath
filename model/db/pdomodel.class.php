<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Handlers\Api;
use CPath\Handlers\ApiField;
use CPath\Handlers\ApiParam;
use CPath\Handlers\ApiSet;
use CPath\Handlers\SimpleApi;
use CPath\Handlers\ValidationException;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Model\Response;

interface IGetDB {
    /**
     * @return PDODatabase
     */
    static function getDB();
}

class ModelNotFoundException extends \Exception {}
class ModelAlreadyExistsException extends \Exception {}

abstract class PDOModel implements IGetDB, IResponseAggregate, IHandlerAggregate {
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
            $row = static::select('*')
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

    /**
     * Returns an IResponse for this object. Defaults to just primary key, if exists.
     * Overwrite this object and return $this->getResponseArray(...) to return more data.
     * @return IResponse
     */
    public function getResponse()
    {
        if(static::Primary)
            return $this->getResponseArray(static::Primary);
        else
            return new Response("Retrieved '" . $this . "'", true, array());
    }

    /**
     * @param String $_keyArgs an array or varargs of all the fields to return in the response. Set to 'ALL' to return all fields.
     * @return Response
     */
    public function getResponseArray($_keyArgs) {
        if($_keyArgs==='ALL') {
            $row = $this->mRow;
        } else {
            $args = is_array($_keyArgs) ? $_keyArgs : func_get_args();
            $row = array();
            foreach($args as $name)
                if($name) $row[$name] = $this->mRow[$name];
        }
        return new Response("Retrieved '" . $this . "'", true, $row);
    }

    public function __toString() {
        if(static::Primary)
            return get_class($this) . " '" . $this->mRow[static::Primary] . "'";
        return get_class($this);
    }


    // Statics

    public static function create(Array $row) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Create " . get_called_class() . " Model");
        foreach($row as $k=>$v)
            if($v===null)
                unset($row[$k]);
        try {
            $id = static::insert(array_keys($row))
                ->requestInsertID(static::Primary)
                ->values(array_values($row))
                ->getInsertID();
        } catch (\PDOException $ex) {
            if(stripos($ex->getMessage(), 'Duplicate')!==false)
                throw new ModelAlreadyExistsException($ex->getMessage(), $ex->getCode(), $ex);
            throw $ex;
        }

        return static::loadByPrimaryKey($id);
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
            throw new \Exception("Constant 'Primary' is not set. Cannot load " . get_called_class() . " Model");
        $Model = static::select('*')
            ->where(static::Primary, $search)
            ->setCallback(get_called_class().'::fetchCallback')
            ->fetch();
        if(!$Model)
            throw new ModelNotFoundException("Model '{$search}' was not found");
        return $Model;
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
     * Searches a Model based on specified fields and values.
     * @param array $fields an array of key-value pairs to search for
     * @param int $limit the number of rows to return
     * @param string $logic 'OR' or 'AND' logic between fields
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    public static function searchByFields(Array $fields, $limit=1, $logic='OR') {
        $Select = static::select('*');

        $i = 0;
        foreach($fields as $k=>$v)
            if($v!==null) {
                if($logic=='OR' && $i++) $Select->where('OR');
                $Select->where($k, $v);
            }

        $Select->limit($limit);
        $Select->setCallback(get_called_class().'::fetchCallback');
        return $Select;
    }


    /**
     * Searches for a Model using all indexed fields.
     * @param mixed $any a value to search for
     * @param int $limit the number of rows to return
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if the model does not contain index keys
     */
     public static function searchByAnyIndex($any, $limit=1) {
        if(!static::SearchKeys)
            throw new \Exception("No Indexes defined in ".get_called_class());
        $Select = static::select('*');

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
        $Select->setCallback(get_called_class().'::fetchCallback');
        return $Select;
    }

    public static function removeByPrimary($id) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . get_called_class() . " Model");
        $c = static::delete()
            ->where(static::Primary, $id)
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete User '{$id}'");
    }


    protected static function removeModel(PDOModel $Model) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . get_called_class() . " Model");
        static::removeByPrimary($Model->mRow[static::Primary]);
    }

    /**
     * @return ApiSet a set of general api handlers for this model.
     * @throws ValidationException
     */
    public static function getApiSet()
    {
        /** @var PDOModel $Class */
        $Class = get_called_class();
        $ApiSet = new ApiSet($Class);
        if(static::Primary) {
            $ApiSet->addApi('get', new SimpleApi(function(Api $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                return $Class::loadByPrimaryKey($request['id']);
            }, array(
                'id' => new ApiParam($Class." ID"),
            )));
        }

        if(static::SearchKeys) {
            $ApiSet->addApi('search', new SimpleApi(function(Api $API, Array $request) use ($Class) {
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
                'search' => new ApiParam("Search for {$Class}"),
                'searchby' => new ApiField("Search by field. Allowed: [".static::SearchKeys."]"),
                'limit' => new ApiField("The Number of fields to return. Max=".static::SearchLimitMax),
            )));
        }

        return $ApiSet;
    }

    /**
     * @return IHandler $Handler
     */
    static function getHandler() {
        return static::getApiSet();
    }
}
