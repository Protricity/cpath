<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/22/14
 * Time: 12:17 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Header\HeaderConfig;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLAjaxSupportHeaders implements IHTMLSupportHeaders
{
//	const CSS_AJAX_SUPPORT = 'html-form-ajax-support';

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		HeaderConfig::writeJQueryHeadersOnce($Head);
		$Head->writeScript(__DIR__ . '/assets/ajax-support.js');
	}
}