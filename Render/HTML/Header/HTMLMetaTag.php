<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/28/2014
 * Time: 12:14 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Request\IRequest;

class HTMLMetaTag implements IHTMLSupportHeaders
{
	const META_TITLE = 'title';
	const META_AUTHOR = 'author';
	const META_DESCRIPTION = 'description';

	private $mName, $mContent;

	public function __construct($name, $content) {
		$this->mName    = $name;
		$this->mContent = $content;
	}

	public function getContent() {
		return $this->mContent;
	}

	public function getName() {
		return $this->mName;
	}



	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeHTML("<meta name='{$this->mName}' content='{$this->mContent}'>");
	}
}