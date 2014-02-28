<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Model\Query;

use CPath\Config;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;

use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Table;

/**
 * Custom select object returns PDOModel instances instead of arrays
 * Class PDOModelSelect the class to instantiate
 * @package CPath\Framework\PDO
 */
class PDOModelSelect extends PDOSelect {

    /**
     * Create a new PDOModelSelect
     * @param \CPath\Framework\PDO\Table\Types\PDOTable $Table A PDOTable instance to select with
     * @param ISelectDescriptor $Descriptor
     * @internal param \PDO $DB the database instance
     */
    public function __construct(Table\Types\PDOTable $Table, ISelectDescriptor $Descriptor=null) {
        parent::__construct($Table, array($Table->getTableName() . '.*'), $Descriptor);
    }

    /**
     * Execute this query
     * @return $this|\PDOStatement
     */
    public function exec() {
        parent::exec();
        $this->mStmt->setFetchMode(\PDO::FETCH_CLASS, $this->getTable()->getModelClass());
        return $this;
    }

    /**
     * @return \CPath\Framework\PDO\Table\Model\Types\PDOModel
     */
    public function fetch() {
        return parent::fetch();
    }
}
