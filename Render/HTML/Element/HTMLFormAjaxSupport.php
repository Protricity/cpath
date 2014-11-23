<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/22/14
 * Time: 12:17 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\HeaderConfig;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLFormAjaxSupport implements IRenderHTML, IHTMLSupportHeaders
{
	const CSS_AJAX_SUPPORT = 'html-form-ajax-support';

	private $mLegendContent = null;

	public function __construct($legendContent="Ajax Form") {
		$this->mLegendContent = $legendContent;
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer inst to use
	 * @return void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeScript(HeaderConfig::$JQueryPath);
		$Head->writeScript(__DIR__ . '/assets/html-form-ajax-support.js');
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Legend = new HTMLElement('legend', self::CSS_AJAX_SUPPORT, $this->mLegendContent);
		$Legend->renderHTML($Request, $Attr, $Parent);
	}
}