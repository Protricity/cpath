<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Base;
use CPath\Handlers\API;
use CPath\Interfaces\IDatabase;
use CPath\Log;
use \PDO;
abstract class PDOAPI extends API {
    abstract function onValidate($callable);
}