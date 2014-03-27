<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Extensions;

use CPath\Framework\PDO\Table\Column;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\IPDOTable;

interface IPDOTableExtensions extends IPDOTable
{
    /**
     * Initialize the table/columns
     * @param PDOColumn[] $Columns
     */
    function initTable($Columns);
}