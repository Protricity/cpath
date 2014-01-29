<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Models;

use CPath\Builders\Tools\BuildPHPClass;

class BuildPDOPKTable extends BuildPDOTable {

    function processModelPHP(BuildPHPClass $PHP) {
        Models\parent::processModelPHP($PHP);
        $PHP->setExtend("CPath\\Model\\DB\\PDOPrimaryKeyModel");
    }
}