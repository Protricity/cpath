<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/21/14
 * Time: 7:13 PM
 */
namespace CPath\Render\HTML\Template;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\HTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\Common\ExceptionResponse;

abstract class AbstractHTMLTemplate extends HTMLContainer implements IHTMLTemplate, IRenderHTML
{

	/**
	 * Write all support headers used by this template
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	abstract protected function writeTemplateHeaders(IRequest $Request, IHeaderWriter $Head);

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		foreach($this->getContent() as $Render) {
			try {
				$Render->renderHTML($Request);
			} catch (\Exception $ex) {
				$Render = new ResponseUtil(new ExceptionResponse($ex));
				$Render->renderHTML($Request);
			}
		}
	}



	/**
	 * Write all support headers used by this template
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	final function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$this->writeTemplateHeaders($Request, $Head);
		parent::writeHeaders($Request, $Head);
	}
}