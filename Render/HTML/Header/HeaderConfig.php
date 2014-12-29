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
	static $RequireJSPath=null;


	static function writeJQueryHeaders(IHeaderWriter $Head) {
		$Head->writeScript(self::$JQueryPath ?: __DIR__ . '/assets/jquery.min.js');
	}
}
