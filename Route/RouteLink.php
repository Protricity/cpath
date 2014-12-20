<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 5:15 PM
 */
namespace CPath\Route;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;

class RouteLink implements IKeyMap
{
    private $mPrefix, $mTarget;

    public function __construct($prefix, $target) {
        $this->mPrefix = $prefix;
        $this->mTarget = $target;
    }

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
//		$url = $this->mPrefix;
//		$method = 'GET';
//		if(strpos($url, ' ') !== false)
//			list($method, $url) = explode(' ', $this->mPrefix);
		$Map->map('prefix', $this->mPrefix);
//		$Map->map('method', $method);
		$Map->map('target', $this->mTarget);
	}
//
//	/**
//	 * Render request as html
//	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
//	 * @param IAttributes $Attr
//	 * @param IRenderHTML $Parent
//	 * @return String|void always returns void
//	 */
//	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
//		$path = $this->mPrefix;
//		if(strpos($this->mPrefix, ' ') !== false)
//			list($method, $path) = explode(' ', $this->mPrefix, 2);
//
//		$Anchor = new HTMLAnchor($path, $this->mPrefix);
//		$Anchor->renderHTML($Request, $Attr, $this);
//	}
}