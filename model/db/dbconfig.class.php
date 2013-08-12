<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Interfaces\IConfig;

class DBConfig implements IConfig{
    static $UpgradeAuto = false;
    static $UpgradeEnable = false;

    function install() {}
}

