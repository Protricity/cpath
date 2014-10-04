<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/2/14
 * Time: 1:33 PM
 */
namespace CPath\Handlers\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Render\HTML\Attribute;
use CPath\Request\IRequest;

class MappedRoute implements IKeyMap // , IRenderHTML
{
    const KEY_ROUTE = 'route';
    const KEY_TARGET = 'target';
    const KEY_URL = 'url';

    private $mArgs;

    public function __construct(Array $args) {
        $this->mArgs = $args;
    }

    public function getRoute() {
        return $this->mArgs[0];
    }

    public function getTarget() {
        return $this->mArgs[1];
    }

    public function getArg($index = 0) {
        return $this->mArgs[2 + $index];
    }

    public function getURL(IRequest $Request=null, $withDomain=false) {
        list(, $path) = explode(' ', $this->getRoute());
        if($Request)
            $path = $Request->getDomainPath($withDomain) . $path;
        return $path;
    }

    /**
     * Map data to the key map
     * @param IKeyMapper $Map the map instance to add data to
     * @internal param \CPath\Request\IRequest $Request
     * @return void
     */
    function mapKeys(IKeyMapper $Map) {
        $Map->map(self::KEY_TITLE, $this->__toString()) ||
        $Map->map(self::KEY_ROUTE, $this->getRoute()) ||
        $Map->map(self::KEY_TARGET, $this->getTarget()) ||
        $Map->map(self::KEY_URL, $this->getURL());
    }

    function __toString() {
        return "Route: " . $this->getRoute();
    }
}