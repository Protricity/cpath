<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/28/2014
 * Time: 12:14 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Render\HTML\Attribute\Attributes;
use CPath\Request\IRequest;

class HTMLMetaTag extends Attributes implements IHTMLSupportHeaders
{
	const META_TITLE = 'title';
	const META_AUTHOR = 'author';
	const META_DESCRIPTION = 'description';
	const META_CONTENT_TYPE = 'content-type';

	public function __construct($name, $content) {
		switch($name) {
			case self::META_CONTENT_TYPE:
				$this->setAttribute('http-equiv', self::META_CONTENT_TYPE);
				break;
			default:
				$this->setAttribute('name', $name);
				break;
		}
		$this->setAttribute('content', $content);
	}

	public function getContent() {
		return $this->getAttribute('content');
	}

	public function getName() {
		return $this->getAttribute('name');
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeHTML("<meta" . $this->getHTMLAttributeString($Request) . ">");
	}
}