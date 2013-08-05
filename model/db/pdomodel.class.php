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
use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIParam;
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIRequiredParam;
use CPath\Handlers\HandlerSet;
use CPath\Handlers\SimpleAPI;
use CPath\Handlers\ValidationException;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IRoutable;
use CPath\Interfaces\IRouteBuilder;
use CPath\Interfaces\IXML;
use CPath\Log;
use CPath\Model\Response;

interface IGetDB {
    /**
     * @return PDODatabase
     */
    static function getDB();
}

class ModelNotFoundException extends \Exception {}
class ModelAlreadyExistsException extends \Exception {}

abstract class PDOModel implements IResponseAggregate, IGetDB, IJSON, IXML, IHandlerAggregate, IRoutable {
    const Build_Ignore = true; // TODO: Title case
    //const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST

    const TableName = null;
    const ModelName = null;
    const Primary = null;
    const Columns = null;
    const Comments = null;
    const Types = null;

    const SearchLimitMax = 100;
    const SearchLimit = 25;
    const SearchWildCard = false;   // true or false

    const CacheEnabled = false;
    const CacheTTL = 300;

    const Search = 'SPIndex'; // 'Public|Protected|None|Index|SPIndex|Primary|Exclude:[field1,field2]|[Include:][field1,field2]';
    const Insert = 'SPIndex'; // 'Public|Protected|None|Index|SPIndex|Primary|Exclude:[field1,field2]|[Include:][field1,field2]';
    const Update = 'SPIndex'; // 'Public|Protected|None|Index|SPIndex|Primary|Exclude:[field1,field2]|[Include:][field1,field2]';
    const Export = 'SPIndex'; // 'Public|Protected|None|Index|SPIndex|Primary|Exclude:[field1,field2]|[Include:][field1,field2]';

    //protected $mRow = null;
    private $mCommit = NULL;

    /**
     * PDOModel Constructor parameters must be optional.
     * No queries should be attempted to load the model from the constructor.
     * Parameters may formatted and additional parameters added in the constructor
     */
    public function __construct() {

    }

    public function setField($field, $value, $commit=true, $validate=NULL) {
        if($validate !== NULL) {
            $value = filter_var($value, $validate);
            if (!$value)
                throw new ValidationException("Field '%s' is not in the valid format");
        }
        if(!$this->mCommit) $this->mCommit = array();
        if($this->$field == $value)
            return $this;
        $this->mCommit[$field] = $value;
        if($commit)
            $this->commitFields();
        $this->$field = $value;
        return $this;
    }

    public function commitFields() {
        if(!($primary = static::Primary))
            throw new \Exception("Constant 'Primary' is not set. Cannot Update table");
        $id = $this->$primary;
        if(!$this->mCommit) {
            Log::u(get_called_class(), "No Fields Updated for ".static::getModelName()." '{$id}'");
            return 0;
        }
        $set = '';
        $DB = static::getDB();
        foreach($this->mCommit as $field=>$value)
            $set .= ($set ? ",\n\t" : '') . "{$field} = ".$DB->quote($value);
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
     * Returns an indexed array of field names for this object filtered by the tokens in constant Export.
     * @param $tokens String the filter to use
     * @return Array
     */
    protected function getFieldList($tokens) {
        $tokens = explode(':', $tokens);
        switch($tokens[0]) {
            case 'Public':
                $R = new \ReflectionObject($this);
                $list = array();
                foreach($R->getProperties(\ReflectionProperty::IS_PUBLIC) as $p)
                    $list[] = $p->name;
                return $list;
            case 'Protected':
                $R = new \ReflectionObject($this);
                $list = array();
                foreach($R->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $p)
                    $list[] = $p->name;
                return $list;
            case 'None':
                return array();
            case 'Index':
                return explode(',', static::Search);
            case 'SPIndex':
                if(static::Primary)
                    return array_merge((array)static::Primary, self::getStringSearchFields());
                return self::getStringSearchFields();
            case 'Primary':
                return (array)static::Primary;
            case 'Exclude':
                if(empty($tokens[1]) || !($tokens = explode(',', $tokens[1])))
                    return array();
                return array_diff(explode(',', static::Columns), $tokens);
            case 'Include':
                if(empty($tokens[1]) || !($tokens = explode(',', $tokens[1])))
                    return array();
                return array_intersect(explode(',', static::Columns), $tokens);
            default:
                if(!$tokens[0] || !($tokens = explode(',', $tokens[0])))
                    return array();
                return array_intersect(explode(',', static::Columns), $tokens);
        }
    }

    /**
     * Returns an associative array of fields and values for this object filtered by the tokens in constant Export.
     * Defaults to just primary key, if exists.
     * Modify const Export to change what data gets exported
     * @return Array
     */
    public function getExportData()
    {
        if(!static::Export)
            return array();
        $export = array();
        foreach($this->getFieldList(static::Export) as $f)
            $export[$f] = $this->$f;
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
     * @return PDOAPIHandlerSet a set of common api routes for this model
     * @throws ValidationException
     * @throws ModelNotFoundException if no Model was found
     */
    function getAggregateHandler() {
        $Handlers = new PDOAPIHandlerSet(get_called_class());
        $Source = $this;
        $comments = $this->getFieldComments();
        if(static::Primary) {
            $Handlers->addAPI('GET', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
                $request = $API->processRequest($Route);
                $Search = $Source::search();
                $Search->where($Source::Primary, $request['id']);
                $Source::limitAPIGet($Search);
                $data = $Search->fetch();
                if(!$data)
                    throw new ModelNotFoundException($Source::getModelName() . " '{$request['id']}' was not found");
                return $data;
            }, array(
                'id' => new APIRequiredParam($this->getModelName() . ' ' . $comments[static::Primary]),
            ), "Get information about this ".$this->getModelName()));
        }

        if(static::Search) {
            $Handlers->addAPI('GET search', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
                $request = $API->processRequest($Route);
                $limit = $request['limit'];
                if($limit < 1 || $limit > $Source::SearchLimitMax)
                    $limit = $Source::SearchLimit;
                $search = $request['search'];
                if($Source::SearchWildCard) {
                    if(strpos($search, '*') !== false)
                        $search = str_replace('*', '%', $search);
                    else
                        $search .= '%';
                }

                if($by = ($request['search_by'])) {
                    $keys = explode(',', $Source::Search);
                    if(!in_array($by, $keys))
                        throw new ValidationException("Invalid 'search_by'. Allowed: [".$Source::Search."]");

                    $Search = $Source::searchByField($Source::SearchWildCard ? $by . ' LIKE ' : $by, $search, $limit);
                }
                else
                    $Search = $Source::searchByAnyIndex($search, $limit, $Source::SearchWildCard ? 'LIKE' : '');
                $Source::limitAPISearch($Search);
                $data = $Search->fetchAll();
                return new Response("Found (".sizeof($data).") ".$Source::getModelName()."(s)", true, $data);
            }, array(
                'search' => new APIRequiredParam("Search for ".$Source::getModelName()),
                'search_by' => new APIParam("Search by field. Allowed: [".static::Search."]"),
                'limit' => new APIField("The Number of fields to return. Max=".static::SearchLimitMax),
            ), "Search for a ".$this->getModelName() ));
        }

        $fields = array();
        foreach($this->getFieldList(static::Insert) as $field)
            if($field != static::Primary)
                $fields[$field] = new APIRequiredField($Source->getModelName() . " " .  $comments[$field]);

        if($fields) {
            $Handlers->addAPI('POST', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
                $request = $API->processRequest($Route);
                $Model = $Source::createFromArray($request);
                $id = '';
                if($field = $Source::Primary)
                    $id = " '" . $Model->$field . "'";
                return new Response("Created " . $Source::getModelName() . "{$id} Sucessfully.", true, $Model);
            }, $fields, "Create a new ".$this->getModelName()));
        }

        if(static::Primary) {
            $fields = array();
            $fields[static::Primary] = new APIRequiredParam("ID of the " . $Source->getModelName() . " to be updated");
            foreach($this->getFieldList(static::Update) as $field)
                if($field != static::Primary)
                    $fields[$field] = new APIField("Update " . $Source->getModelName() . " " .  $comments[$field]);
            if(sizeof($fields) > 1) {
                $Handlers->addAPI('PATCH', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
                    $request = $API->processRequest($Route);
                    $id = $request[$Source::Primary];
                    unset($request[$Source::Primary]);

                    $Search = $Source::search();
                    $Search->where($Source::Primary, $id);
                    $Source::limitAPIGet($Search);
                    /** @var PDOModel $Model */
                    $Model = $Search->fetch();
                    if(!$Model)
                        throw new ModelNotFoundException($Source::getModelName() . " '{$id}' was not found");

                    foreach($request as $field => $value)
                        if($value !== NULL && $value !== "") {
                            $call = 'set' . str_replace('_', '', $field);
                            try {
                                $Model->$call($value, false);
                            } catch (ValidationException $ex) {
                                throw $ex->updateMessage($field);
                            }
                        }
                    $c = $Model->commitFields();
                    if(!$c)
                        return new Response("No fields were updated for " . $Source::getModelName()." '{$id}'.", true, $Model);
                    return new Response("Updated {$c} Field(s) for " . $Source::getModelName()." '{$id}'.", true, $Model);

                }, $fields, "Update a ".$this->getModelName()));
            }


            $Handlers->addAPI('DELETE', new SimpleAPI(function(API $API, IRoute $Route) use ($Source) {
                $request = $API->processRequest($Route);
                $Search = $Source::search();
                $Search->where($Source::Primary, $request['id']);
                $Source::limitAPIRemove($Search);
                $Model = $Search->fetch();
                if(!$Model)
                    throw new ModelNotFoundException($Source::getModelName() . " '{$request['id']}' was not found");
                $Source::removeModel($Model);
                return new Response("Removed ".$Source::getModelName()."(s)", true, $Model);

            }, array(
                'id' => new APIRequiredParam($Source->getModelName() . " " .  $comments[static::Primary]),
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
     * Creates a new Model based on the provided row of key value pairs
     * @param array $row key value pairs to insert into new row
     * @return PDOModel|null returns NULL if no primary key is available
     * @throws ModelAlreadyExistsException
     * @throws \Exception|\PDOException
     * @throws ValidationException if a field fails to validate
     */
    public static function createFromArray(Array $row) {
       foreach($row as $k=>$v)
            if($v===null)
                unset($row[$k]);
        try {
            $ValidModel = new static;
            foreach($row as $field => $value)
                if($value !== NULL && $value !== "") {
                    $call = 'set' . str_replace('_', '', $field);
                    try {
                        if(method_exists($ValidModel, $call))
                            $ValidModel->$call($value, false);
                    } catch (ValidationException $ex) {
                        throw $ex->updateMessage($field);
                    }
                }

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
     * @param array $fields an array of key-value pairs to search for
     * @param string $logic 'OR' or 'AND' logic between fields
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    public static function loadByFields(Array $fields, $logic='OR', $throwIfNotFound=true) {
        $Model = static::searchByFields($fields, 1, $logic)
            ->fetch();
        if(!$Model && $throwIfNotFound)
            throw new ModelNotFoundException(static::getModelName() . " was not found");
        return $Model;
    }

    /**
     * Loads a model based on a search field
     * @param $fieldName String the database field to search for
     * @param $value String the field value to search for
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    public static function loadByField($fieldName, $value, $throwIfNotFound=true) {
        return static::loadByFields(array($fieldName => $value), NULL, $throwIfNotFound);
    }

    /**
     * Loads a Model using all indexed fields.
     * @param mixed $search a value to search for
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    public static function loadByAnyIndex($search, $throwIfNotFound=true) {
        $Model = static::searchByAnyIndex($search)
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
     * @param mixed $search a value to search for
     * @param int $limit the number of rows to return
     * @param String $compare custom comparison (ex. '<', 'LIKE', '=func(?)')
     * @return PDOSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if the model does not contain index keys
     */
    public static function searchByAnyIndex($search, $limit=1, $compare=NULL) {
        if(!static::Search)
            throw new \Exception("No Indexes defined in ".static::getModelName());
         $Select = static::search();

        $i = 0;
        if(is_numeric($search))
            $keys = explode(',', static::Search);
        else
            $keys = static::getStringSearchFields();

        foreach($keys as $key){
            if($i++) $Select->where('OR');
            if($compare) $key .= ' ' . $compare;
            $Select->where($key, $search);
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


    protected static function removeModel(PDOModel $Model) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . static::getModelName());
        static::removeByPrimary($Model->{static::Primary});
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


    public static function getModelName() {
        return static::ModelName ?: basename(get_called_class());
    }

    private static function getStringSearchFields() {
        $columns = explode(',', static::Columns);
        $search = explode(',', static::Search);
        $fields = array();
        $types = static::Types;
        foreach($columns as $i => $col)
            if($types[$i] != 'i' && in_array($col, $search))
                $fields[] = $col;
        return $fields;
    }

    private static function getFieldComments() {
        $columns = explode(',', static::Columns);
        if(static::Comments) {
            $comments = explode(';', static::Comments);
            return array_combine($columns, $comments);
        }
        $comments = array();
        foreach($columns as $column)
            $comments[$column] = ucwords(str_replace('_', ' ', $column));
        return $comments;
    }
}
PDOModel::init();


class PDOSelectObject extends PDOSelect {
    private $mClass;
    public function __construct($table, \PDO $DB, $Class) {
        parent::__construct($table, $DB, array($table . '.*'));
        $this->mClass = $Class;
    }

    public function exec() {
        parent::exec();
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->mClass);
        return $this;
    }
}
