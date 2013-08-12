<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;
use CPath\Config\Builder;
use CPath\Interfaces\IConfig;


class Compile implements IConfig {
    static $BuildInc = NULL;
    static $RouteMax = NULL;

    static function commit() {
        $Builder = new Builder(new self, Config::getGenPath().'compile.gen.php', true);
        $Builder->commit();
    }

    function install() {
        //$Builder->build(new Compile, Config::$ConfigPath, false);
    }
}

if(!(include Config::getGenPath().'compile.gen.php'))
    Compile::commit();