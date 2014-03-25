<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Types;

use CPath\Cache\Cache;
use CPath\Config;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelAlreadyExistsException;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Log;

abstract class PDOPrimaryKeyTable extends PDOTable {
    const COLUMN_PRIMARY = null;

    const CACHE_ENABLED = false;
    const CACHE_TTL = 300;
//
//    /**
//     * Returns the default IHandlerSet collection for this PDOModel type.
//     * Note: if this method is called in a PDOModel thta does not implement IRoutable, a fatal error will occur
//     * @param bool $readOnly
//     * @param bool $allowDelete
//     * @return RoutableSet a set of common routes for this PDOModel type
//     */
//    function loadDefaultRouteSet($readOnly=true, $allowDelete=false) {
//        $Routes = parent::loadDefaultRouteSet($readOnly, $allowDelete);
//
//        //$Routes['GET :api'] = new APIMultiView($Routes);
//        //$Routes['POST :api'] = new APIMultiView($Routes);
//        $Routes['GET'] = new GetAPI($this);
//        if(!$readOnly) {
//            $Routes['PATCH'] = new PatchAPI($this);
//            if(!$allowDelete)
//                $Routes['DELETE'] = new DeleteAPI($this);
//        } else if($allowDelete) {
//            error_log('allowDelete == true while $readOnly != true');
//        }
//
//        $Routes->setDefault($Routes['GET search'], true);
//        //$Routes->setDefault($Routes['GET'], true);
//        return $Routes;
//    }

    /**
     * Internal method inserts an associative array into the database.
     * Overwritten methods must include parent::insertRow($row);
     * @param array $row
     */
    protected function insertRow(Array $row) {
        if(isset($row[static::COLUMN_PRIMARY]))
            $id = $row[static::COLUMN_PRIMARY];
        $Insert = static::insert(array_keys($row))
            ->requestInsertID(static::COLUMN_PRIMARY)
            ->values(array_values($row));
        if(!isset($id))
            $id = $Insert->getInsertID();
        self::$mLastModelID = $id;
        Log::u(get_called_class(), "Created " . static::getModelName() . " '{$id}'");
    }

    /**
     * Load last inserted PDOModel for this class
     * @return PDOModel
     * @throws \InvalidArgumentException
     */
    final protected function loadLastInsertModel() {
        return static::loadByPrimaryKey(static::loadLastInsertID());
    }

    /**
     * Load last insert ID
     * @return int
     * @throws \InvalidArgumentException
     */
    final protected function loadLastInsertID() {
        if(!self::$mLastModelID)
            throw new \InvalidArgumentException("Model was not inserted");
        return self::$mLastModelID;
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a new instance
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOPrimaryKeyModel the created model instance
     * @throws ModelAlreadyExistsException
     * @throws \CPath\Framework\API\Exceptions\ValidationException if a column fails to validate
     */
    final function createAndLoad($row) {
        static::createFromArray($row);
        return static::loadLastInsertModel();
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a new instance
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOPrimaryKeyModel the created model instance
     * @throws ModelAlreadyExistsException
     * @throws \CPath\Framework\API\Exceptions\ValidationException if a column fails to validate
     */
    final function createOrLoad($row) {
        $Model = static::search()
            ->whereAll($row)
            ->fetch();
        if($Model)
            return $Model;
        static::createFromArray($row);
        return static::loadLastInsertModel();
    }

    // Database methods

    /**
     * Loads a model based on a primary key column value
     * @param $id String the primary key value to search for
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @return PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     * @throws \Exception if the model does not contain primary keys
     */
    final function loadByPrimaryKey($id, $throwIfNotFound=true) {
        if(static::CACHE_ENABLED
            && $Model = static::$mCache->fetch(get_called_class() . ':id:' . $id))
            return $Model;
        $Model = static::search()
            ->where(static::COLUMN_PRIMARY, $id)
            ->fetch();
        if(!$Model) {
            if($throwIfNotFound)
                throw new ModelNotFoundException($this, $id);
            return NULL;
        }
        if(static::CACHE_ENABLED)
            static::$mCache->store(get_called_class() . ':id:' . $id, $Model);
        return $Model;
    }

    /**
     * Delete a model entry by PRIMARY Key Column
     * @param $id mixed the PRIMARY Key to search for
     * @throws \Exception
     * @throws \InvalidArgumentException if $id is invalid
     */
    final function removeByPrimary($id) {
        if($id === NULL)
            throw new \InvalidArgumentException("Remove ID can not be NULL. Cannot Delete " . static::getModelName());
        $c = static::delete()
            ->where(static::COLUMN_PRIMARY, $id)
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete ".static::getModelName()." '{$id}'");
        if(static::CACHE_ENABLED)
            static::$mCache->remove(get_called_class() . ':id:' . $id);
    }

    /**
     * Remove the row associated with a model from the database
     * @param PDOModel $Model the model to remove
     * @throws \Exception if no primary key is identified for this model
     */
    final function removeModel(PDOModel $Model) {
        static::removeByPrimary($Model->{static::COLUMN_PRIMARY});
        Log::u(get_called_class(), "Deleted " . $Model);
    }



    // Statics

    /**
     * @var Cache
     */
    protected static $mCache = NULL;
    private static $mLastModelID = NULL;

    /**
     * // TODO: refactor less crappy?
     * Initialize this class
     */
    final public static function __pk_init() {
        self::$mCache = Cache::get();
    }
}
PDOPrimaryKeyTable::__pk_init();
