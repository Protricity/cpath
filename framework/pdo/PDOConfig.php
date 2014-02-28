<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;
use CPath\Interfaces\IConfig;

class PDOConfig implements IConfig{
    static $UpgradeAuto = false;
    static $UpgradeEnable = false;

    function install() {
        if(__CLASS__ != get_called_class())
            throw new \Exception(__CLASS__ . "::install() may only be called from an non-inherited instance of " . __CLASS__);
    }
}

