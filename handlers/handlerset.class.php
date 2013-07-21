<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\IRoute;
use CPath\NoRoutesFoundException;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandler;
use CPath\Model\MultiException;
use CPath\Model\Response;
use CPath\Model\ResponseException;
use CPath\Builders\BuildRoutes;
use CPath\Handlers\API\View\APIInfo;

/**
 * Class HandlerSet
 * @package CPath\Handlers
 *
 * Provides a Handler Set for Handler calls
 */
class HandlerSet implements IHandler {

    const Build_Ignore = true;     // This class should not be built. Classes that use it should set Build_Ignore to false

    const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const Route_Path = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IHandler[] */
    protected $mHandlers = array();

    public function addHandler($path, IHandler $Handler) {
        $this->mHandlers[strtolower($path)] = $Handler;
        return $this;
    }

    public function getHandler($path) {
        $path = strtolower($path);
        return isset($this->mHandlers[$path]) ? $this->mHandlers[$path] : NULL;
    }

    function render(IRoute $Route)
    {
        $path = $Route->getNextArg();
        if(!$path)
            throw new NoRoutesFoundException("Route is missing. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        if(!isset($this->mHandlers[$path]))
            throw new NoRoutesFoundException("Route '{$path}' is missing invalid. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        $Route->addToRoute($path);
        $this->mHandlers[$path]->render($Route);
    }
}
