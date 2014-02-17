<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Model\Interfaces;

use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Framework\Data\Serialize\Interfaces\ISerializable;

interface IPDOModel extends IMappable, ISerializable {

    /**
     * @return \CPath\Framework\PDO\Table\Types\PDOTable
     */
    function table();
}

