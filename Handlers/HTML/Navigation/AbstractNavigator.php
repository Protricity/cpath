<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/3/14
 * Time: 3:36 PM
 */
namespace CPath\Handlers\HTML\Navigation;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\IRequestHandlerAggregate;
use CPath\Route\DefaultMap;
use CPath\Route\IRouteMap;
use CPath\Route\IRouteMapper;

abstract class AbstractNavigator implements IRenderHTML, IRouteMapper
{
    private $mRoutable;
    /** @var \CPath\Request\IRequest */
    private $mRequest;

    public function __construct(IRouteMap $Routable = null)
    {
        $this->mRoutable = $Routable ? : new DefaultMap();
    }

    /**
     * Begin NavBar render
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    abstract function renderStart(IRequest $Request, IAttributes $Attr = null);

    /**
     * Render NavBar link
     * @param \CPath\Request\IRequest $Request
     * @param RouteLink $Link
     * @return String|void always returns void
     */
    abstract function renderLink(IRequest $Request, RouteLink $Link);

    /**
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    abstract function renderEnd(IRequest $Request);

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @internal param $ \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        $this->mRequest = $Request;
        $this->renderStart($Request, $Attr);

        $this->mRoutable->mapRoutes($this);

        $this->renderEnd($Request);
    }

    /**
     * Map data to a key in the map
     * @param String $prefix
     * @param \CPath\Request\IRequestHandlerAggregate $Destination
     * @return bool if true the mapping will discontinue
     */
    function mapRoute($prefix, IRequestHandlerAggregate $Destination)
    {
        $Request = $this->mRequest;
        list($method, $path) = explode(' ', $prefix, 2);
        $requestRoute = $Request->getRoute();
        list($requestMethod, $requestPath) = explode(' ', $requestRoute, 2);

        $matched = false;
        if ($method === 'ANY' || $method == $requestMethod) {
            if (strpos($requestPath, $path) === 0) {
                $matched = true;
            }
        }

        $this->renderLink($Request, new RouteLink($Destination, $path, $matched));
    }
}