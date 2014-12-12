<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/2/14
 * Time: 1:33 PM
 */
namespace CPath\Route;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\URL\URLValue;
use CPath\Request\IRequest;

class MappedRoute implements IKeyMap // , IRenderHTML
{
	const KEY_TITLE = 'title';
	const KEY_ROUTE = 'route';
    const KEY_TARGET = 'target';
    const KEY_URL = 'url';

    private $mArgs;
	private $mRoute;
	private $mTarget;

    public function __construct($route, $target, Array $args=array()) {
	    $this->mRoute = $route;
	    $this->mTarget = $target;
        $this->mArgs = $args;
    }

    public function getRoute() {
        return $this->mRoute;
    }

    public function getTarget() {
        return $this->mTarget;
    }

    public function getArg($index = 0) {
        return $this->mArgs[$index];
    }

    public function getLink() {
        return new RouteLink($this->getRoute(), $this->getTarget());
    }

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
    function mapKeys(IKeyMapper $Map) {
        //$Map->map(self::KEY_TITLE, $this->__toString()) ||
        $Map->map(self::KEY_ROUTE, $this->getRoute()) ||
        $Map->map(self::KEY_TARGET, $this->getTarget()) ||
        $Map->map(self::KEY_URL, $this->getLink());
    }

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$this->getLink()
			->renderHTML($Request, $Attr);
	}

    function __toString() {
        return "Route: " . $this->getRoute();
    }
}