<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
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

abstract class PDOModel implements IGetDB, IResponseAggregate {
    const TableName = null;
    const Primary = null;
    protected $mRow;
    private $mCommit = array();

    public function __construct($id) {
        if(is_array($id)) {
            $row = $id;
        } else {
            $row = static::getDB()->select(static::TableName, '*')
                ->where(static::Primary, $id)
                ->fetch();
        }
        if(!$row)
            throw new ModelNotFoundException(get_class($this) . " '{$id}' not found");
        $this->mRow = $row;
    }

    public function setField($field, $value, $commit=true) {
        $this->mCommit[$field] = $value;
        if($commit) {
            if(!($primary = static::getPrimaryKeyField()))
                throw new \Exception("Constant 'Primary' is not set. Cannot Update table");
            $set = '';
            $DB = static::getDB();
            foreach($this->mCommit as $field=>$value)
                $set .= ($set ? ",\n\t" : '') . "{$field} = ".$DB->quote($value);
            $SQL = "UPDATE ".static::TableName
                ."\n SET {$set}"
                ."\n WHERE ".$primary." = ".$DB->quote($this->mRow[$primary]);
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
        return new Response("Retrieved '" . $this . "'", true, $this->mRow);
    }

    public function __toString() {
        if($p = static::getPrimaryKeyField())
            return get_class($this) . " '" . $this->mRow[$p] . "'";
        return get_class($this);
    }


    // Statics

    protected static function getPrimaryKeyField() {
        return static::Primary;
    }

    protected static function createA(Array $row) {
        if(!($primary = static::getPrimaryKeyField()))
            throw new \Exception("Constant 'Primary' is not set. Cannot Create " . get_called_class() . " Model");
        $DB = static::getDB();
        foreach($row as $k=>$v)
            if($v===null)
                unset($row[$k]);
        try {
            $id = $DB->insert(static::TableName, array_keys($row))
                ->requestInsertID($primary)
                ->values(array_values($row))
                ->getInsertID();
        } catch (\PDOException $ex) {
            if(strpos($ex->getMessage(), 'Duplicate')!==false)
                throw new ModelAlreadyExistsException($ex->getMessage(), $ex->getCode(), $ex);
            throw $ex;
        }

        return new static($id);
    }

    protected static function searchA(Array $row, $limit=100) {
        if(!($primary = static::getPrimaryKeyField()))
            throw new \Exception("Constant 'Primary' is not set. Cannot Create " . get_called_class() . " Model");
        $DB = static::getDB();

        $Select = $DB->select(static::TableName, '*');
        foreach($row as $k=>$v)
            if($v!==null)
                $Select->where($k, $v);

        $Class = get_called_class();
        $Select->setCallback(function(Array $row) use ($Class) {
            return new $Class($row);
        });

        return $Select;
    }

    protected static function deleteM(PDOModel $Model) {
        if(!($primary = static::getPrimaryKeyField()))
            throw new \Exception("Constant 'Primary' is not set. Cannot Delete " . get_called_class() . " Model");
        $DB = static::getDB();
        $c = $DB->delete(static::TableName)
            ->where($primary, $Model->mRow[$primary])
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete User '" . $Model->mRow[$primary] . "'");
    }
}
