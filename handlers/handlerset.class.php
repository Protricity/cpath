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
use CPath\NoRoutesFoundException;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandler;
use CPath\Model\MultiException;
use CPath\Model\Response;
use CPath\Model\ResponseException;
use CPath\Builders\BuildRoutes;
use CPath\Handlers\Api\View\ApiInfo;

/**
 * Class HandlerSet
 * @package CPath\Handlers
 *
 * Provides a Handler Set for Handler calls
 */
class HandlerSet implements IHandler {

    const BUILD_IGNORE = true;     // This class should not be built. Classes that use it should set BUILD_IGNORE to false

    const ROUTE_METHODS = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;        // No custom route path. Path is based on namespace + class name

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

    function render(Array $args)
    {
        $path = strtolower(array_shift($args));
        if(!$path)
            throw new NoRoutesFoundException("Route is missing. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        if(!isset($this->mHandlers[$path]))
            throw new NoRoutesFoundException("Route '{$path}' is missing invalid. Possible routes are: ".implode(', ', array_keys($this->mHandlers)));
        $this->mHandlers[$path]->render($args);
    }
}
