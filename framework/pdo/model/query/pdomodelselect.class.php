<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Model\Query;

use CPath\Config;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Table;

/**
 * Custom select object returns PDOModel instances instead of arrays
 * Class PDOModelSelect the class to instantiate
 * @package CPath\Framework\PDO
 */
class PDOModelSelect extends PDOSelect {
    private $mTable;

    /**
     * Create a new PDOModelSelect
     * @param \PDO $DB the database instance
     * @param \CPath\Framework\PDO\Table\PDOTable $Table A PDOTable instance to select with
     * @param ISelectDescriptor $Descriptor
     */
    public function __construct(\PDO $DB, Table\PDOTable $Table, ISelectDescriptor $Descriptor=null) {
        $table = $Table::TABLE;
        parent::__construct($table, $DB, array($table . '.*'), $Descriptor);
        $this->mTable = $Table;
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
        $this->mStmt->setFetchMode(\PDO::FETCH_CLASS, $this->mTable->getModelClass());
        return $this;
    }

    /**
     * @return PDOModel
     */
    public function fetch() {
        return parent::fetch();
    }
}
