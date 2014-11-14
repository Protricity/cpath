<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 7:01 PM
 */
namespace CPath\Render\HTML\Theme;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\HTMLAttributes;
use CPath\Render\HTML\Element\HTMLForm;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class FormThemeAttributes extends HTMLAttributes implements IHTMLSupportHeaders
{
	const DEFAULT_FORM_THEME_CSS = 'cpath-default-form-theme';

	public function __construct($themeClass = self::DEFAULT_FORM_THEME_CSS) {
		$this->addClass($themeClass);
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		//$Head->writeScript(__DIR__ . '\assets\cpath-default-form-theme.js');
		$Head->writeStyleSheet(__DIR__ . '\assets\cpath-default-form-theme.css');
	}
}