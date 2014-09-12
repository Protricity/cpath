<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Helpers;

use CPath\Build\Code\BuildPHPClass;

interface IPDOTableHelper {

    function processPHP(BuildPHPClass $PHP);
}

