<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 3:22 PM
 */
namespace CPath\Render\HTML\Theme;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Element\HTMLFormSection;
use CPath\Render\HTML\Header\HeaderConfig;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLFormSectionTheme implements IHTMLSupportHeaders
{
	public function __construct(HTMLFormSection $Section, $closed=false) {
		$Section->addClass('html-form-section-theme');
		if($closed)
			$Section->Legend->addClass('closed');
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeScript(HeaderConfig::$JQueryPath);
		$Head->writeScript(__DIR__ . '/assets/html-form-section-theme.js');
	}
}