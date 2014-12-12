<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 11:33 AM
 */
namespace CPath\Data\Value;


use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class URLValue implements IRenderHTML, IKeyMap, IHasURL
{
	private $mURL;
	private $mValue;
	public function __construct($url, $value=null) {
		$this->mURL = $url;
		$this->mValue = $value;
	}

	public function getURL(IRequest $Request=null, $withDomain=true) {
		if($Request && strpos($this->mURL, 'http') === false) {
			$domainPath = $Request->getDomainPath($withDomain);
			if(strpos($this->mURL, $domainPath) === false)
				return $domainPath . $this->mURL;
		}
		return $this->mURL;
	}

	public function getValue() {
		return $this->mValue ?: $this->mURL;
	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		// TODO: Implement mapKeys() method.
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		// TODO: Implement renderHTML() method.
	}

	function __toString() {
		// TODO: Implement __toString() method.
	}


}