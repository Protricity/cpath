<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Model;

use CPath\Framework\PDO\Table\PDOTable;
use CPath\Framework\Response\Interfaces\IResponseAggregate;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IXML;
use CPath\Serializer\ISerializable;

interface IPDOModel extends IResponseAggregate, IJSON, IXML, ISerializable {

    /**
     * @return PDOTable
     */
    function table();
}

