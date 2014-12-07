<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/6/14
 * Time: 11:13 PM
 */
namespace CPath\Render\HTML\Attribute;

use CPath\Request\IRequest;

class URLAttribute extends Attributes
{
	function __construct($attrValue, $attrName = 'href') {
		parent::__construct($attrName, $attrValue);
	}

	public function getURL(IRequest $Request) {
		$url        = parent::getValue($Request);
		$domainPath = $Request->getDomainPath();
		if (strpos($url, $domainPath) === false)
			$url = $domainPath . $url;

		return $url;
	}

	public function getValue(IRequest $Request = null) {
		return $this->getURL($Request);
	}
}