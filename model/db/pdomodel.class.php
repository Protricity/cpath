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
use CPath\Handlers\HandlerSet;
use CPath\Handlers\SimpleApi;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IResponseAggregate;
use CPath\Model\Response;
use \PDO;

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

    const TableName = null;
    const Primary = null;
    const SearchKeys = null;
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
            $row = static::getDB()->select(static::TableName, '*')
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
                ."\n WHERE ".static::Primary." = ".$DB->quote($this->mRow[$primary]);
            $DB->exec($SQL);
            $this->mCommit = array();
        }
        $this->mRow[$field] = $value;
        return $this;
    }

    /**
     * @return IResponse
     */
    public function getResponse()
    {
        $row = array();
        if(static::Primary)
            $row[static::Primary] = $this->mRow[static::Primary];
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
        $DB = static::getDB();
        foreach($row as $k=>$v)
            if($v===null)
                unset($row[$k]);
        try {
            $id = $DB->insert(static::TableName, array_keys($row))
                ->requestInsertID(static::Primary)
                ->values(array_values($row))
                ->getInsertID();
        } catch (\PDOException $ex) {
            if(stripos($ex->getMessage(), 'Duplicate')!==false)
                throw new ModelAlreadyExistsException($ex->getMessage(), $ex->getCode(), $ex);
            throw $ex;
        }

        return new static($id);
    }

    protected static function fetchCallback(Array $row) {
        /** @var PDOModel $M */
        $M = new static();
        $M->setData($row);
        return $M;
    }

    /**
     * Loads a model based on a primary key column value
     * @param $search String the value to search for
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     * @throws \Exception if the model does not contain primary keys
     */
    public static function loadByPrimaryKey($search) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot load " . get_called_class() . " Model");
        $DB = static::getDB();
        $Model = $DB->select(static::TableName, '*')
            ->where(static::Primary, $search)
            ->setCallback(get_called_class().':fetchCallback')
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
        $DB = static::getDB();
        $Select = $DB->select(static::TableName, '*');

        $i = 0;
        foreach($fields as $k=>$v)
            if($v!==null) {
                if($logic=='OR' && $i++) $Select->where('OR');
                $Select->where($k, $v);
            }

        $Select->limit($limit);
        $Select->setCallback(get_called_class().':fetchCallback');
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
        $DB = static::getDB();
        $Class = get_called_class();
        if(!static::SearchKeys)
            throw new \Exception("No Indexes defined in ".$Class);
        $Select = $DB->select(static::TableName, '*');

        $i = 0;
        foreach(explode(',', static::SearchKeys) as $key){
            if($i++) $Select->where('OR');
            $Select->where($key, $any);
        }

        $Select->limit($limit);
        $Select->setCallback(get_called_class().':fetchCallback');
        return $Select;
    }

    protected static function deleteM(PDOModel $Model) {
        if(!static::Primary)
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . get_called_class() . " Model");
        $DB = static::getDB();
        $c = $DB->delete(static::TableName)
            ->where(static::Primary, $Model->mRow[static::Primary])
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete User '" . $Model->mRow[static::Primary] . "'");
    }

    /**
     * @return IHandler $Handler
     */
    public static function getHandler()
    {
        /** @var PDOModel $Class */
        $Class = get_called_class();
        $Handlers = new HandlerSet();
        if(static::Primary) {
            $Handlers->addHandler('get', new SimpleApi(function(Api $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                return $Class::search(array($Class::Primary => $request['search']));
            }, array(
                'id' => new ApiParam(),
            )));
        }

        if(static::SearchKeys) {
            $keys = explode(',', static::SearchKeys);
            foreach($keys as $key)
                $fields[$key] = new ApiField("Search by ".ucfirst($key));
            $Handlers->addHandler('search', new SimpleApi(function(Api $API, Array $request) use ($Class) {
                $request = $API->processRequest($request);
                return $Class::search(array($Class::Primary => $request['search']));
            }, $fields));
        }
    }
}
