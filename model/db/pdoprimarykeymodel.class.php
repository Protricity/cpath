<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;

use CPath\Cache\Cache;
use CPath\Config;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Handlers\HandlerSet;
use CPath\Handlers\Views\APIMultiView;
use CPath\Handlers\Views\APIView;
use CPath\Handlers\Views\Routes\APIViewRoute;
use CPath\Log;
use CPath\Route\RoutableSet;

abstract class PDOPrimaryKeyModel extends PDOModel {
    const PRIMARY = null;

    const CACHE_ENABLED = false;
    const CACHE_TTL = 300;

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
     * UPDATE column values for this Model
     * @return int the number of columns updated
     * @throws \Exception if no primary key exists
     */
    function commitColumns() {
        $primary = static::PRIMARY;
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
        $this->mCommit = NULL;
        if(static::CACHE_ENABLED)
            static::$mCache->store(get_called_class() . ':id:' . $id, $this, static::CACHE_TTL);
        return $c;
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
        $this->mCommit[$column] = $value;
        if($commit)
            $this->commitColumns();
        $this->$column = $value;
        return $this;
    }

    /**
     * Returns the default IHandlerSet collection for this PDOModel type.
     * Note: if this method is called in a PDOModel thta does not implement IRoutable, a fatal error will occur
     * @param bool $readOnly
     * @param bool $allowDelete
     * @return RoutableSet a set of common routes for this PDOModel type
     */
    function loadDefaultRouteSet($readOnly=true, $allowDelete=false) {
        $Routes = parent::loadDefaultRouteSet($readOnly, $allowDelete);

        //$Routes['GET :api'] = new APIMultiView($Routes);
        //$Routes['POST :api'] = new APIMultiView($Routes);
        $Routes['GET'] = new API_Get($this);
        if(!$readOnly) {
            $Routes['PATCH'] = new API_Patch($this);
            if(!$allowDelete)
                $Routes['DELETE'] = new API_Delete($this);
        } else if($allowDelete) {
            error_log('allowDelete == true while $readOnly != true');
        }

        $Routes->setDefault($Routes['GET search'], true);
        //$Routes->setDefault($Routes['GET'], true);
        return $Routes;
    }

    /**
     * Load column values for an active instance
     * @param String $_columns a varargs of strings representing columns
     * @return Array an array of column values
     * @throws \Exception if there is no PRIMARY key for this table
     */
    function loadColumnValues($_columns) {
        return self::select(func_get_args())
            ->where(static::PRIMARY, $this->columnValue(static::PRIMARY))
            ->fetch();
    }

    public function __toString() {
        if($id = static::HANDLER_IDS ?: static::PRIMARY)
            return static::modelName() . " '" . $this->$id . "'";
        return parent::modelName();
    }


    // Statics

    /**
     * @var Cache
     */
    protected static $mCache = NULL;
    private static $mLastModelID = NULL;

    /**
     * Initialize this class
     */
    final public static function init() {
        self::$mCache = Cache::get();
    }

    /**
     * Internal method inserts an associative array into the database.
     * Overwritten methods must include parent::insertRow($row);
     * @param array $row
     */
    protected static function insertRow(Array $row) {
        if(isset($row[static::PRIMARY]))
            $id = $row[static::PRIMARY];
        $Insert = static::insert(array_keys($row))
            ->requestInsertID(static::PRIMARY)
            ->values(array_values($row));
        if(!isset($id))
            $id = $Insert->getInsertID();
        self::$mLastModelID = $id;
        Log::u(get_called_class(), "Created " . static::modelName() . " '{$id}'");
    }

    /**
     * Load last inserted PDOModel for this class
     * @return PDOModel
     * @throws \InvalidArgumentException
     */
    final protected static function loadLastInsertModel() {
        return static::loadByPrimaryKey(static::loadLastInsertID());
    }

    /**
     * Load last insert ID
     * @return int
     * @throws \InvalidArgumentException
     */
    final protected static function loadLastInsertID() {
        if(!self::$mLastModelID)
            throw new \InvalidArgumentException("Model was not inserted");
        return self::$mLastModelID;
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a new instance
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOPrimaryKeyModel the created model instance
     * @throws ModelAlreadyExistsException
     * @throws ValidationException if a column fails to validate
     */
    final static function createAndLoad($row) {
        static::createFromArray($row);
        return static::loadLastInsertModel();
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a new instance
     * @param array|mixed $row column value pairs to insert into new row
     * @return PDOPrimaryKeyModel the created model instance
     * @throws ModelAlreadyExistsException
     * @throws ValidationException if a column fails to validate
     */
    final static function createOrLoad($row) {
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
    final static function loadByPrimaryKey($id, $throwIfNotFound=true) {
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
     * Delete a model entry by PRIMARY Key Column
     * @param $id mixed the PRIMARY Key to search for
     * @throws \Exception
     * @throws \InvalidArgumentException if $id is invalid
     */
    final static function removeByPrimary($id) {
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
    final static function removeModel(PDOModel $Model) {
        static::removeByPrimary($Model->{static::PRIMARY});
        Log::u(get_called_class(), "Deleted " . $Model);
    }

}
PDOPrimaryKeyModel::init();
