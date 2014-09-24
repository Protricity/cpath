<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Model\Interfaces;

use CPath\Data\Map\IMappableKeys;
use CPath\Framework\Data\Serialize\Interfaces\ISerializable;
use CPath\Response\IResponse;

interface IPDOModel extends IMappableKeys, ISerializable {

    /**
     * @return \CPath\Framework\PDO\Table\Types\PDOTable
     */
    function table();
}

