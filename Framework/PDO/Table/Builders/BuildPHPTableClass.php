<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/5/14
 * Time: 10:38 AM
 */
namespace CPath\Framework\PDO\Table\Builders;

use CPath\Build\Code\BuildPHPClass;

class BuildPHPTableClass extends BuildPHPClass
{
    public function __construct($name, $filePath, $namespace=null) {
        parent::__construct($name, $filePath, $namespace);
    }
}