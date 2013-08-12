<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\Base;
use CPath\Build;
use CPath\Compile;
use CPath\Config;
use CPath\Exceptions\BuildException;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IRoutable;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IRouteBuilder;
use CPath\Log;
use CPath\Model\Route;
use CPath\Router;
use CPath\RouterAPC;
use CPath\Util;

/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class RouteBuilder implements IRouteBuilder, IBuildable {
    const TMPL_ROUTES = <<<'PHP'
<?php
%s
$routes = array(%s
);
PHP;

    const TMPL_CUSTOM_ROUTES = <<<'PHP'
<?php
use CPath\Model\Route;
$routes = array(
//  new Route('GET /path/to', 'My\Handler', array('my'=>'custom', 'request'=>'parameters')),
);
PHP;

    private $mRoutes = array();
    /** @var IHandler */
    private $mCurrentClass = NULL;

    /**
     * Performs a build on a class. If the class is not a type that should be built,
     * this method should return false immediately
     * @param IBuildable $Buildable
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\Exceptions\BuildException when a build exception occurred
     */
    public function build(IBuildable $Buildable) {
        if($Buildable instanceof IHandlerAggregate)
            $Buildable = $Buildable->getAggregateHandler();
        if(!$Buildable instanceof IHandler)
            return false;
        $this->mCurrentClass = $Buildable;
        $routes = $this->getHandlerRoutes($Buildable);
        foreach($routes as $Route)
            $this->addRoute($Route);
        return true;
    }

    private function getCurrentClass() {
        if(!$this->mCurrentClass)
            throw new BuildException("No current class available");
        return $this->mCurrentClass;
    }

    private function getCustomRoutes() {
        $routes = array();
        $path = Config::getGenPath().'routes.custom.php';
        if(file_exists($path)) {
            include $path;
        } else {
            file_put_contents($path, self::TMPL_CUSTOM_ROUTES);
        }

        return $routes;
    }

    /**
     * Add a route to the builder
     * @param IRoute $Route the route instance to add
     * @param bool $replace if true, replace any existing routes
     * @throws \CPath\Exceptions\BuildException if a route already exists and $replace==false
     */
    private function addRoute(IRoute $Route, $replace=false) {
        if(!$replace && isset($this->mRoutes[$Route->getPrefix()])) {
            throw new BuildException("Route Prefix already exists: ".$Route->getPrefix());
        }
        $this->mRoutes[$Route->getPrefix()] = $Route;
    }

    /**
     * Checks to see if any routes were built, and builds them into [gen]/routes.php
     */
    public function buildComplete() {
        if(!$this->mRoutes)
            return;
        /** @var IRoute $Route */
        foreach($this->getCustomRoutes() as $Route)
            $this->addRoute($Route);

//        foreach($this->mRoutes as &$route)
//            if($route['match'][strlen($route['match'])-1] != '/')
//                $route['match'] .= '/';
        usort($this->mRoutes, function (IRoute $a, IRoute $b){
            $b = $b->getPrefix();
            $a = $a->getPrefix();
            return (substr_count($b, '/')-substr_count($a, '/'));
        });

        $max = 0;
        $useClass = array();
        $i=0;
        foreach($this->mRoutes as $Route) {
            if(!isset($useClass[get_class($Route)]))
                $useClass[get_class($Route)] = 'Route' . ($i++ ?: '');
            if(($l = strlen($Route->getPrefix())) > $max)
                $max = $l;
        }


        $phpUse = '';
        foreach($useClass as $class=>$alias)
            $phpUse .= "\nuse " . $class . " as " . $alias . ";";
        $phpRoute = '';
        foreach($this->mRoutes as $Route) {
            $args = '';
            $i=0;
            foreach($Route->getExportArgs() as $arg)
                $args .= ($i++ ? ', ' : '') . var_export($arg, true);
            $phpRoute .= "\n\tnew " . $useClass[get_class($Route)] . '(' . $args . '),';
        }
        $output = sprintf(self::TMPL_ROUTES, $phpUse, $phpRoute);
        $path = Config::getGenPath().'routes.gen.php';
        if(!is_dir(dirname($path)))
            mkdir(dirname($path), NULL, true);
        file_put_contents($path, $output);
        Log::v(__CLASS__, count($this->mRoutes) . " Route(s) rebuilt.");

        if(Config::$APCEnabled)
            self::rebuildAPCCache();

        Compile::$RouteMax = $max;
        Compile::commit();

        $this->mRoutes = array();
    }

    /**
     * Returns a list of allowed route methods associated with this class
     * @param String|Array $methods an array of methods or a string list delimited by '|'
     * @return array a validated list of methods
     * @throws \CPath\Exceptions\BuildException if no class was specified and no default class exists
     */
    public function parseMethods($methods) {
        //$methods = $Handler::ROUTE_METHODS ?: 'GET,POST,CLI';

        $allowed = explode(',', IRoute::METHODS);
        if(!is_array($methods))
            $methods = explode(',', $methods);
        foreach($methods as &$method) {
            $method = strtoupper($method);
            if($method == 'ANY') {
                $methods = $allowed;
                break;
            }
            if(!in_array($method, $allowed))
                throw new BuildException("Method '{$method}' is not supported");
        }
        return $methods;
    }

    /**
     * Get all default routes for this Handler
     * @param String|IHandler $Handler The class instance or class name
     * @param String|Array|null $methods the allowed methods
     * @param String|null $path the route path or null for default
     * @return array
     */
    function getHandlerDefaultRoutes($Handler, $methods='GET,POST,CLI', $path=NULL) {
        $methods = $this->parseMethods($methods ?: 'GET,POST,CLI');
        if(!$path)
            $path = $this->getHandlerDefaultPath($Handler);
        $routes = array();
        foreach($methods as $method)
            $routes[$method] = new Route($method . ' ' . $path, get_class($Handler));
        return $routes;
    }

    /**
     * Gets the default public route path for this handler
     * @param String|IHandler $Handler The class instance or class name
     * @return string The public route path
     */
    function getHandlerDefaultPath($Handler) {
        $path = str_replace('\\', '/', strtolower(get_class($Handler)));
        if($path[0] !== '/')
            $path = '/' . $path;
        return $path;
    }

    /**
     * Determines the Handler route(s) from constants or the class name
     * @param IHandler $Handler|NULL The class instance or NULL for the current class
     * @return IRoute[] a list of IRoute instances
     * @throws \CPath\Exceptions\BuildException when a build exception occurred
     */
    private function getHandlerRoutes(IHandler $Handler=NULL) {
        if(!$Handler)
            $Handler = $this->getCurrentClass();

        $routes = array();
        if($Handler instanceof IRoutable) {
            $routes = $Handler->getAllRoutes($this);
        } else {
            $R = new \ReflectionClass($Handler);
            $path = $R->getConstant('ROUTE_PATH');
            $method = $R->getConstant('ROUTE_METHODS');
            $routes = $this->getHandlerDefaultRoutes($Handler, $method, $path);
        }
        return $routes;
    }

    // Statics

    public static function rebuildAPCCache() {
        if(Base::isCLI())
            return;
        Log::e(__CLASS__, "Rebuilding APC Cache");
        $c = 0;
        $cache = apc_cache_info('user');
        if($cache)
            foreach($cache['cache_list'] as $info) {
                if(strpos($info['info'], RouterAPC::PREFIX) === 0) {
                    apc_delete($info['info']);
                    $c++;
                }
            }
        elseif($cache===false) {
            Log::e(__CLASS__, "APC Cache info could not be got. Make sure to set apc.enable_cli=1 in php.ini");
            return;
        }
        Log::v(__CLASS__, "Cleared {$c} APC Entries");

        $routes = Router::getRoutes();
        /** @var IRoute $Route */
        foreach($routes as $Route)
            apc_store(RouterAPC::PREFIX_ROUTE.$Route->getPrefix(), $Route);

        Log::v(__CLASS__, "Stored (%s) into APC Cache", sizeof($routes));
    }

    public static function createBuildableInstance() {
        return new static;
    }
}
