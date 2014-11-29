<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;
use CPath\Framework\Build\API\Build;
use CPath\Interfaces\IConfig;
use CPath\Request\RequestSelector;

class Config  {
    static $GenPath = 'gen';
//    static $Paths = array(
//        'ANY /' => 'CPath\Framework\View\Templates\CLI\CLIErrorView',
//    );

    static $Domain = NULL;
    static $SiteName = NULL;
    static $Debug = false;
    static $BuildEnabled = true;
    static $APCEnabled = false;
    static $LogLevel = 2;

    static $ValidationUsername = array();
    static $ValidationPassword = array();

    //static $AllowCLIRequest = false;

    static $ProfileEnable = false;

    /**
     * Choose session request inst.
     * Note: Override to replace method
     * @return Request\IRequest
     */
    static function chooseRequest() {
        $Chooser = new RequestSelector();
        return $Chooser->getRequest();
    }

    static function getGenPath() {
        return Base::getBasePath() . self::$GenPath . '/';
    }

    static function getDomainPath() {
        return self::$Domain ?: self::$Domain = Build::get()->buildDomainPath();
    }

    static function getSiteName() {
        return self::$SiteName ?: self::$SiteName = parse_url(self::$Domain, PHP_URL_HOST);
    }

    static function init() {
        $path = dirname(__DIR__) . '/config.php';
        if(!file_exists($path) || !(include $path))
            Config::setDefaults();
    }

    private static function setDefaults() {
        self::$Domain = self::getDomainPath();
        self::$SiteName = self::getSiteName();
        self::$APCEnabled = function_exists('apc_fetch');
    }

    function install() {
        if(__CLASS__ != get_called_class())
            throw new \Exception(__CLASS__ . "::install() may only be called from an non-inherited inst of " . __CLASS__);
        //$path = dirname(__DIR__) . '/config.php';
        //$Builder = new Builder($this, $path, true);
    }
}
Config::init();