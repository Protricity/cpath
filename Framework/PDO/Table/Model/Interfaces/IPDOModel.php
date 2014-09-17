<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Model\Interfaces;

use CPath\Data\Map\IMappable;
use CPath\Framework\Data\Serialize\Interfaces\ISerializable;
use CPath\Framework\Response\Interfaces\IResponse;

interface IPDOModel extends IMappable, ISerializable {

    /**
     * @return \CPath\Framework\PDO\Table\Types\PDOTable
     */
    function table();
}

