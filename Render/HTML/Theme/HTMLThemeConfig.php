<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/14
 * Time: 10:52 AM
 */
namespace CPath\Render\HTML\Theme;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Header\HTMLHeaders;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLThemeConfig
{
	static $DefaultFormTheme = 'cpath-default-form-theme';

	/** @var IHTMLSupportHeaders[] */
	private static $ClassHeaders = array();

	public static function writeThemeHeaders(IRequest $Request, IHeaderWriter $Head, $selector) {
		$args = is_array($selector) ? $selector : preg_split('/\s+/', $selector);
		foreach(array_values($args) as $selector) {
			if(($p = strpos($selector, '.')) > 0)
				$args[] = substr($selector, $p);
		}
		foreach($args as $selector) {
			$selector = strtolower($selector);

			if(isset(self::$ClassHeaders[$selector])) {
				self::$ClassHeaders[$selector]->writeHeaders($Request, $Head);
				continue;
			}
		}
	}

	public static function registerThemeHeaders($selector, IHTMLSupportHeaders $Headers) {
		$selector = strtolower($selector);
		self::$ClassHeaders[$selector] = $Headers;
	}
}

HTMLThemeConfig::registerThemeHeaders('form.cpath-default-form-theme', new HTMLHeaders(
	__DIR__ . '\assets\cpath-default-form-theme.css',
	__DIR__ . '\assets\cpath-default-form-theme.js'
));