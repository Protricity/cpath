<?php
namespace CPath\Handlers\Views\Routes;

use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Handlers\Views\APIView;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Route\InvalidHandlerException;
use CPath\Route\Route;

class APIViewRouteDELETE extends Route {

    /**
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function renderDestination(IRequest $Request) {
        $API = $this->loadHandler();
        $View = new APIView($API);
        $View->render($Request);
    }

    // Static

    /**
     * @param IAPI $API
     * @return APIViewRouteDELETE
     */
    static function fromAPI(IAPI $API) {
        $Route = static::fromHandler($API);
        return $Route;
    }
}