<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Model\Helpers;

use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Builders\Tables\BuildPDOTable;
use CPath\Framework\PDO\Builders\Tables\BuildPHPTableClass;
use CPath\Framework\PDO\DB\PDODatabase;

interface IPDOBuilder {

    /**
     * Process PHP classes for a PDO Builder
     * @param PDODatabase $DB
     * @param BuildPDOTable $Table
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Exceptions\BuildException
     * @return void
     */
    function processPHP(PDODatabase $DB, BuildPDOTable $Table, BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel);
}

