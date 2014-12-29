<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/28/2014
 * Time: 2:33 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Header\HTMLMetaTag;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLHeaderContainer;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLHeaderContainer implements IHTMLSupportHeaders, IHTMLHeaderContainer
{

	/** @var IHTMLSupportHeaders[] */
	private $mSupportHeaders = array();


	public function addSupportHeaders(IHTMLSupportHeaders $Headers, IHTMLSupportHeaders $_Headers = null) {
		foreach (func_get_args() as $Headers)
			$this->mSupportHeaders[] = $Headers;
	}

	/**
	 * @return IHTMLSupportHeaders[]
	 */
	function getSupportHeaders() {
		return $this->mSupportHeaders;
	}

	public function getMetaTagContent($name) {
		foreach ($this->getSupportHeaders() as $Header) {
			if ($Header instanceof HTMLMetaTag) {
				if ($Header->getName() === $name) {
					return $Header->getContent();
				}
			}
		}

		return null;
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		foreach ($this->mSupportHeaders as $Headers)
			$Headers->writeHeaders($Request, $Head);
	}

}