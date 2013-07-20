<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\BuildException;
use CPath\Base;
use CPath\Interfaces\IBuilder;
use CPath\Log;


/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class BuildRoutes implements IBuilder {
    const IHandler = "CPath\\Interfaces\\IHandler";
    const IHandlerAggregate = "CPath\\Interfaces\\IHandlerAggregate";
    const METHODS = 'GET|POST|PUT|DELETE|CLI';
    private $mRoutes = array();

    /**
     * Performs a build on a class. If the class is not a type that should be built,
     * this method should return false immediately
     * @param \ReflectionClass $Class
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\BuildException when a build exception occured
     */
    public function build(\ReflectionClass $Class) {
        if(!$Class->implementsInterface(self::IHandler) && !$Class->implementsInterface(self::IHandlerAggregate))
            return false;

        foreach($this->getHandlerRoutes($Class) as $route) {
            $this->mRoutes[] = array(
                'match' => $route,
                'class' => $Class->getName(),
            );
        }
        return true;
    }

    /**
     * Checks to see if any routes were built, and builds them into [gen]/routes.php
     */
    public function buildComplete() {
        if(!$this->mRoutes)
            return;

        $output = "<?php\n\$routes = array(";
        foreach($this->mRoutes as $route)
            $output .= "\n\tarray('" . $route['match'] . "', '" . $route['class'] . "'),";
        $output .= "\n);";
        $path = Base::getGenPath().'routes.php';
        if(!is_dir(dirname($path)))
            mkdir(dirname($path), NULL, true);
        file_put_contents($path, $output);
        Log::v(__CLASS__, count($this->mRoutes) . " Route(s) rebuilt.");

        $this->mRoutes = array();
    }

    /**
     * Determines the Handler route(s) from constants or the class name
     * @param \ReflectionClass $Class
     * @return array a list of routes
     * @throws \CPath\BuildException when a build exception occured
     */
    public function getHandlerRoutes(\ReflectionClass $Class) {
        $routes = array();
        $methods = $Class->getConstant('ROUTE_METHODS') ?: 'GET|POST|CLI';
        $route = $Class->getConstant('ROUTE_PATH');
        if(!$route) {
            $route = '/'.str_replace('\\', '/', strtolower($Class->getName()));
        }
        if(preg_match('/^([A-Z|]+) (.*)$/i', $route, $matches)) {
            list($full, $methods, $route) = $matches;
        }

        $allowed = explode('|', self::METHODS);
        $methods = explode('|', $methods);
        foreach($methods as &$method) {
            $method = strtoupper($method);
            if($method == 'ALL') {
                $methods = $allowed;
                break;
            }
            if(!in_array($method, $allowed))
                throw new BuildException("Method '{$method}' is not supported");
        }

        foreach($methods as $m) {
            $routes[] = $m . ' ' . $route;
        }

        return $routes;
    }
}
