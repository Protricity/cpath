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
    static $DomainPath = '/';
	function getDomainPath($path) {
		return self::$DomainPath . $path;
	}
}
