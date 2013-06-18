<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

class DestinationNotFoundException extends \Exception {}
class InvalidHandlerException extends \Exception {}
class NoRoutesFoundException extends \Exception {}

class Route {

    private
        $mRoute,
        $mDestination;

    public function __construct($route, $destination) {
        $this->mRoute = $route;
        $this->mDestination = $destination;
    }

    public function tryRoute($requestPath=NULL) {
        if($requestPath === NULL)
            $requestPath = Util::getUrlRoute();

        if(strpos($requestPath, $this->mRoute) !== 0)
            return false;

        $args = explode('/', substr($requestPath, strlen($this->mRoute) + 1));

        $dest = $this->mDestination;
        if(file_exists($dest)) {
            $Handler = new FileHandler($dest);
        } elseif(class_exists($dest)) {
            $Handler = new $dest();
            if(!($Handler instanceof Interfaces\IHandler))
                throw new InvalidHandlerException("Destination '{$dest}' is not a valid IHandler");
        } else {
            throw new DestinationNotFoundException("Destination {$dest} could not be found");
        }

        $Handler->render($args);
        return true;
    }

    public static function tryAllRoutes() {
        $routes = array();
        include Base::getGenPath().'routes.php';
        foreach($routes as $route) {
            $Route = new Route($route[0], $route[1]);
            if($Route->tryRoute())
                return;
        }
        throw new NoRoutesFoundException("No Routes Matched: " . Util::getUrlRoute());
    }
}