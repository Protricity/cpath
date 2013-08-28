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

class Config implements IConfig {
    static $BasePath = NULL;
    static $GenPath = 'gen';

    static $Domain = NULL;
    static $Debug = true;
    static $BuildEnabled = true;
    static $APCEnabled = false;
    static $LogLevel = NULL;

    static $ValidationUsername = array();
    static $ValidationPassword = array();

    static $AllowCLIRequest = false;

    static $ProfileEnable = false;

    static function getGenPath() {
        return self::$BasePath . self::$GenPath . '/';
    }

    static function init() {
        $path = dirname(__DIR__) . '/config.php';
        if(file_exists($path))
            include $path;
        else
            Config::setDefaults();
    }

    private static function setDefaults() {
        self::$Domain = Build::buildDomainPath();
        self::$BasePath = dirname(__DIR__) . '/';
        self::$APCEnabled = function_exists('apc_fetch');
    }

    function install() {
        $path = dirname(__DIR__) . '/config.php';
        $Builder = new Builder($this, $path, true);
    }
}
Config::init();