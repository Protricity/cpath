<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Builders\Interfaces;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\DB\PDODatabase;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn;
use CPath\Framework\PDO\Table\IPDOTable;

interface IPDOTableBuilder extends IPDOTable {

    /**
     * Process PHP classes for a PDO Builder
     * @param PDODatabase $DB
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @internal param \CPath\Framework\PDO\Table\Builders\Interfaces\IPDOTableBuilder $Table
     * @return void
     */
    function processPHP(PDODatabase $DB, BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel);

    /**
     * Add a column to this table builder
     * @param BuildPDOColumn $Column
     */
    function addColumn(BuildPDOColumn $Column);

    function getTableClass();
    function getModelClass();
}

