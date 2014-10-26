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
use CPath\Request\IRequest;

class HTMLFormAjax extends HTMLForm
{

	const CSS_FORM_AJAX = 'html-form-ajax';

	/**
	 * @param null $method
	 * @param null $action
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
	 * @param null $_content
	 */
	public function __construct($method = null, $action = null, $classList = null, $_content = null) {
		parent::__construct($method, $action, $classList);
		if ($_content !== null)
			$this->addAll(array_slice(func_get_args(), 3));
		$this->addClass(self::CSS_FORM_AJAX);
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer instance to use
	 * @return void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeScript(HeaderConfig::$JQueryPath);
		$Head->writeScript(__DIR__ . '/assets/html-form-ajax.js');
		parent::writeHeaders($Request, $Head);
	}
}