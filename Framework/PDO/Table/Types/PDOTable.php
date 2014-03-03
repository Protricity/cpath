<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:46 AM
 */
namespace CPath\Framework\PDO\Table\Types;

use CPath\Config;
use CPath\Framework\Api\Exceptions\ValidationException;
use CPath\Framework\Build\IBuildable;
use CPath\Framework\PDO\Query\PDODelete;
use CPath\Framework\PDO\Query\PDOInsert;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOUpdate;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\Column\Collection\Types\PDOColumnCollection;
use CPath\Framework\PDO\Table\Column\Exceptions\ColumnNotFoundException;
use CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Extensions\IPDOTableExtensions;
use CPath\Framework\PDO\Table\IPDOTable;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelAlreadyExistsException;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Model\Query\PDOModelSelect;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Interfaces\IArrayObject;
use CPath\Log;

abstract class PDOTable implements IPDOTable, IBuildable
{

    //const MODEL_CLASS = null;
    const TABLE = null;

    const COLUMN_ID = NULL; // Identifier column used in such endpoints as GET, PATCH, DELETE. Defaults to ::COLUMN_PRIMARY or ::COLUMN_ID
    const COLUMN_TITLE = NULL; // Title column used to describe a model instance. Defaults to ::COLUMN_ID

    const SEARCH_LIMIT_MAX = 100;
    const SEARCH_LIMIT = 25;
    const SEARCH_WILDCARD = false; // true or false

    const EXPORT_AS_OBJECT = false; // Export as array by default.

    //const EXPORT_SEARCH = NULL;    // ':None|:Index|:SIndex|:Primary|:Exclude:[column1,column2]|:Include:[column1,column2]';

    // TODO: get rid of all these consts
    const DEFAULT_FILTER = FILTER_SANITIZE_SPECIAL_CHARS;

    // Auto generate short names for all fields (for use in CLI)
    //const AUTO_SHORTS = false;

    // TODO: get rid of all these consts
    const SECURITY_DISABLED = false;

    const ROUTE_METHOD = null;

    /** @var PDOColumnCollection|IPDOColumn[] */
    private $mColumns, $mModelClass;

    /**
     * Note: PDOTable Constructor parameters must be optional.
     */
    protected function __construct($_cols=null) {
        $Columns = $this->mColumns = new PDOColumnCollection();

        foreach(func_get_args() as $Column) {
            $Columns->add($Column);
        }

        if($this instanceof IPDOTableExtensions)
            $this->initTable($Columns);
    }

//
//    /**
//     * Returns the default IHandlerSet collection for this PDOModel type.
//     * Note: if this method is called in a PDOModel thta does not implement IRoutable, a fatal error will occur
//     * @param bool $readOnly
//     * @param bool $allowDelete
//     * @return RoutableSet a set of common routes for this PDOModel type
//     */
//    function loadDefaultRouteSet($readOnly=true, $allowDelete=false) {
//        $Routes = RoutableSet::fromHandler($this);
//        $Routes['GET :api'] = new APIMultiView($Routes);
//        $Routes['POST :api'] = new APIMultiView($Routes);
//
//        $Routes['GET search'] = new GetSearchAPI($this);
//        $Routes['GET browse'] = new GetBrowseAPI($this);
//        if(!$readOnly)
//            $Routes['POST'] = new PostAPI($this);
//
//        $Routes->setDefault($Routes['GET :api']);
//        return $Routes;
//    }

    /**
     * Returns the table name
     * @return string the model class name
     */
    public function getTableName() {
        return static::TABLE;
    }

    /**
     * Returns the model class name
     * @return string|PDOModel the model class name
     */
    public function getModelClass() {
        return $this->mModelClass;
    }

    /**
     * Sets the model class for query instances
     * @param String $class the PDOModel class to instantiate
     */
    public function setModelClass($class) {
        $this->mModelClass = $class;
    }

    /**
     * Returns the model name from comment or the class name
     * @return string the model name
     */
    public function getModelName() {
        $class = $this->getModelClass();
        return $class::modelName();
    }

    /**
     * Return information for a column
     * @param String $name the name of the column
     * @return \CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn
     * @throws ColumnNotFoundException if the column was not found
     */
    final function getColumn($name) {
        if (!isset($this->mColumns[$name]))
            throw new ColumnNotFoundException("Column '{$name}' could not be found in " . $this->getModelName());
        return $this->mColumns[$name];
    }

    /**
     * Return all table columns
     * @return PDOColumnCollection|IPDOColumn[]
     */
    final function getColumns() {
        return $this->mColumns;
    }

    /**
     * Returns an indexed array of column names for this object filtered by the tokens in constant EXPORT.
     * @param $tokens String|Array|int the column list (comma delimited), array, token or filter to use, or NULL for all columns
     * @return PDOColumn[] associative array of $columns and config data
     * @throws \Exception if an invalid token was used
     */
    final function findColumns($tokens) {
        if (is_int($tokens)) {
            $list = array();
            foreach ($this->mColumns as $col => $data)
                if ($data->hasFlag($tokens))
                    $list[$col] = $data;
            return $list;
        }
        if (!is_array($tokens)) {
            if ($tokens[0] == ':') {
                $tokens = explode(':', $tokens);
                switch (strtolower($tokens[1])) {
                    case ':none':
                        return array();
                    case ':index':
                        return $this->findColumns(PDOColumn::FLAG_INDEX);
                    case ':sindex':
                        $list = array();
                        foreach ($this->mColumns as $col => $data)
                            if ($data->hasFlag(PDOColumn::FLAG_INDEX) && !$data->hasFlag(PDOColumn::FLAG_NUMERIC))
                                $list[$col] = $data;
                        return $list;
                    case ':primary':
                        return $this->findColumns(PDOColumn::FLAG_PRIMARY);
                    case ':exclude':
                        if (empty($tokens[2]) || !($tokens = explode(',', $tokens[2])))
                            return array();
                        return array_diff_key($this->mColumns, array_flip($tokens));
                    case ':include':
                        if (empty($tokens[2]) || !($tokens = explode(',', $tokens[2])))
                            return array();
                        return array_intersect_key($this->mColumns, array_flip($tokens));
                    default:
                        throw new \Exception("Invalid Identifier: " . $tokens[1]);
                }
            }

            if (!($tokens = explode(',', $tokens)))
                return array();
        }

        $ret = array();
        foreach($tokens as $token) {
            $found = false;
            foreach($this->mColumns as $Column) {
                $name = $Column->getName();
                if($name == $token) {
                    $ret[$name] = $Column;
                    $found = true;
                    break;
                }
            }

            if(!$found)
                throw new \Exception("Column not found in " . $this->getModelName() . ": " . implode(', ', $tokens));
        }

//        $cols = $this->mColumns;
//        $ret = array();
//        foreach ($tokens as $token)
//            $found = false;
//            foreach($this->mColumns as $Column)
//                if($Column->getName() == $token) {
//                    $found = true;
//                    break;
//                }
//            if (isset($cols[$token])) {
//                $ret[$token] = $cols[$token];
//            } else {
//                throw new \Exception("Column '{$token}' not found in " . $this->getModelName());
//            }
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

    protected function insertRow(Array $row)
    {
        $this->insert(array_keys($row))
            ->values(array_values($row));
        Log::u(get_called_class(), "Created " . $this->getModelName());
    }

    /**
     * Creates a new Model based on the provided row of column value pairs
     * @param array|mixed $row column value pairs to insert into new row
     * @return void
     * @throws ModelAlreadyExistsException
     * @throws \Exception|\PDOException
     * @throws ValidationException if a column fails to validate
     */
    final public function createFromArray($row)
    {
        if ($row instanceof IArrayObject)
            $row = $row->getDataPath();

        foreach ($this->mColumns as $Column)
            if ($Column->getDefaultValue())
                if (!isset($row[$Column->getName()]))
                    $row[$Column->getName()] = $Column->getDefaultValue();

        foreach ($row as $k => $v)
            if ($v === null)
                unset($row[$k]);
        try {
//            foreach($row as $column => $value) // No re-validation
//                static::validateColumn($column, $value);
            $this->insertRow($row);
            return;
        } catch (\PDOException $ex) {
            if (stripos($ex->getMessage(), 'Duplicate') !== false) {
                $err = "A Duplicate " . $this->getModelName() . " already exists";
                if (Config::$Debug)
                    $err .= ': ' . $ex->getMessage();
                Log::u(get_called_class(), "Duplicate " . $this->getModelName() . " already exists");
                throw new ModelAlreadyExistsException($err, $ex->getCode(), $ex);
            }
            throw $ex;
        }
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a model instance
     * Note: This model instance is NOT pulled from the database, but is instead filled with the row values.
     * @param array|mixed $row column value pairs to insert into new row
     * @return \CPath\Framework\PDO\Table\Model\Types\PDOModel the new instance with filled values
     * @throws ModelAlreadyExistsException
     * @throws \CPath\Framework\Api\Exceptions\ValidationException if a column fails to validate
     */
    final public function createAndFill($row)
    {
        $this->createFromArray($row);
        $Model = $this->getModelClass();
        return $Model::unserialize($row);
    }

    /**
     * Creates a new Model based on the provided row of column value pairs and returns a new instance
     * @param array|mixed $row column value pairs to insert into new row
     * @return \CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel the created model instance
     * @throws ModelAlreadyExistsException
     * @throws ValidationException if a column fails to validate
     */
    final function createOrFill($row)
    {
        $Model = $this->search()
            ->whereAll($row)
            ->fetch();
        if ($Model)
            return $Model;
        return $this->createAndFill($row);
    }

    // Database methods

    /**
     * Create a PDOModelSelect object for this table
     * @param $_selectArgs array|mixed an array or series of varargs of columns to select
     * @return PDOSelect
     */
    final function select($_selectArgs = NULL) {
        $args = func_num_args() > 1 ? func_get_args() : (Array)$_selectArgs;
        return new PDOSelect($this, $args);
    }

    /**
     * Applies a search to PDOSelect based on specified columns and values.
     * @param String|int|Array|PDOSelect $Select the columns to return or the PDOSelect instance to search in
     * @param String|Array $search the column value to search for or an associative array of column/value pairs to search for.
     * Note: If an array is passed here, the $columns value is ignored.
     * @param mixed $columns a string list (comma delimited) or array of columns to search for.
     * Default is columns with PDOColumn::FLAG_SEARCH set
     * @param int $limit the number of rows to return. Default is 1
     * @param string $logic 'OR' or 'AND' logic between columns. Default is 'OR'
     * @param string|NULL $compare set WHERE logic for each column [=, >, LIKE, etc]. Default is '='
     * @return PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if no columns were found
     */
    final function selectByColumns($Select, $search, $columns = NULL, $limit = 1, $logic = 'OR', $compare = NULL) {
        if (!$Select instanceof PDOSelect)
            $Select = $this->select($Select);

        if (strcasecmp($logic, 'OR') === 0)
            $Select->setFlag(PDOWhere::LOGIC_OR);

        if (!is_array($search)) {

            if ($columns instanceof IArrayObject)
                $columns = $columns->getDataPath();
            $columns = $this->findColumns($columns ? : PDOColumn::FLAG_SEARCH);
            if (!$columns)
                throw new \Exception("No SEARCH fields defined in " . $this->getModelName());
            foreach ($columns as $name => $Column) {
                if ($compare) $name .= " {$compare} ";
                $Select->where($name, $search);
            }

        } else {
            foreach ($search as $name => $value)
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
    final function insert($_insertArgs) {
        $DB = $this->getDB();
        $args = func_num_args() > 1 ? func_get_args() : array_keys($this->findColumns($_insertArgs));
        return $DB->insert($this, $args);
    }

    /**
     * Create a PDOUpdate object for this table
     * @param $_columnArgs array|mixed an array or series of varargs of columns to be updated
     * @return \CPath\Framework\PDO\Query\PDOUpdate
     */
    final function update($_columnArgs) {
        $args = func_num_args() > 1 ? func_get_args() : array_keys($this->findColumns($_columnArgs));
        return new PDOUpdate($this, $args);
    }

    /**
     * Create a PDODelete object for this table
     * @return PDODelete
     */
    final function delete() {
        return new PDODelete($this);
    }

    /**
     * Loads a model based on a search
     * @param String|Array $search the column value to search for or an associative array of column/value pairs to search for.
     * Note: If an array is passed here, the $columns value is ignored.
     * @param mixed $columns a string list (comma delimited) or array of columns to search for.
     * Default is columns with PDOColumn::FLAG_SEARCH set
     * @param boolean $throwIfNotFound if true, throws an exception if not found
     * @param string $logic 'OR' or 'AND' logic between columns
     * @return \CPath\Framework\PDO\Table\Model\Types\PDOModel the found model instance
     * @throws ModelNotFoundException if a model entry was not found
     */
    final public function loadByColumns($search, $columns = NULL, $throwIfNotFound = true, $logic = 'OR') {
        $Model = $this->searchByColumns($search, $columns, 1, $logic)
            ->fetch();
        if (!$Model && $throwIfNotFound) {
            throw new ModelNotFoundException($this->getModelName() . " '{$search}' was not found");
        }
        return $Model;
    }

    /**
     * Creates a PDOModelSelect for searching models.
     * @return PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     */
    final public function search() {
        return new PDOModelSelect($this);
    }

    final public function __toString() {
        return $this->getTableName();
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
     * @return \CPath\Framework\PDO\Table\Model\Query\PDOModelSelect - the select query. Use ->fetch() or foreach to return model instances
     * @throws \Exception if no columns were found
     */
    final public function searchByColumns($search, $columns = NULL, $limit = 1, $logic = 'OR', $compare = NULL) {
        return $this->selectByColumns($this->search(), $search, $columns, $limit, $logic, $compare);
    }

    // Statics

    /**
     * Return an instance of the class for building purposes
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance()
    {
        $R = new \ReflectionClass(get_called_class());
        if (!$R->isAbstract())
            return new static;
        return null;
    }

    /**
     * Return the full class name via get_called_class
     * @return String the Class name
     */
    final static function cls() { return get_called_class(); }
}
