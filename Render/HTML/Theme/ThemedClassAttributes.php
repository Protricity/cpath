<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 7:01 PM
 */
namespace CPath\Render\HTML\Theme;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\Attributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class ThemedClassAttributes extends Attributes implements IHTMLSupportHeaders
{
	const DEFAULT_FORM_THEME_CSS = 'cpath-default-form-theme';

	/** @var IHTMLSupportHeaders[] */
	private $mSupportHeaders = array();

	public function __construct($themeClass = self::DEFAULT_FORM_THEME_CSS) {
		$this->addClass($themeClass);
	}

	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @return void
	 */
	function addSupportHeaders(IHTMLSupportHeaders $Headers) {
		$this->mSupportHeaders[] = $Headers;
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		//$Head->writeScript(__DIR__ . '\assets\cpath-default-form-theme.js');
		$Head->writeStyleSheet(__DIR__ . '\assets\cpath-default-form-theme.css');
		foreach($this->mSupportHeaders as $Headers)
			$Headers->writeHeaders($Request, $Head);
	}
}