<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 7:27 PM
 */
namespace CPath\Render\HTML\Header;

class HeaderConfig
{
	static $JQueryPath=null;
}
HeaderConfig::$JQueryPath = HeaderConfig::$JQueryPath ?: __DIR__ . '/assets/jquery.min.js';