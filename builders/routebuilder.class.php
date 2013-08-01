<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\Build;
use CPath\BuildException;
use CPath\Base;
use CPath\Interfaces\IBuilder;
use CPath\Interfaces\IRoutable;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\IRouteBuilder;
use CPath\Log;
use CPath\Model\Route;
use CPath\Router;
use CPath\RouterAPC;

/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class RouteBuilder implements IRouteBuilder {
    const IHandler = "CPath\\Interfaces\\IHandler";
    const IHandlerAggregate = "CPath\\Interfaces\\IHandlerAggregate";
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
    private $mCurrentClass = NULL;

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
        $this->mCurrentClass = $Class;
        $routes = $this->getHandlerRoutes($Class);
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
        $path = Base::getGenPath().'routes.custom.php';
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
     * @throws \CPath\BuildException if a route already exists and $replace==false
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
            return substr_count($b->getPrefix(), '/')-substr_count($a->getPrefix(), '/');
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
            while($arg = $Route->getNextArg())
                $args .= ', ' . var_export($arg, true);
            $phpRoute .= "\n\tnew " . $useClass[get_class($Route)]
                . "(" . var_export($Route->getPrefix(), true)
                . ", " . var_export($Route->getDestination(), true)
                . $args
                ."),";
        }
        $output = sprintf(self::TMPL_ROUTES, $phpUse, $phpRoute);
        $path = Base::getGenPath().'routes.php';
        if(!is_dir(dirname($path)))
            mkdir(dirname($path), NULL, true);
        file_put_contents($path, $output);
        Log::v(__CLASS__, count($this->mRoutes) . " Route(s) rebuilt.");

        if(Base::isApcEnabled())
            self::clearAPCCache();

        Base::commitConfig('route.max', $max);

        $this->mRoutes = array();
    }

    /**
     * Returns a list of allowed route methods associated with this class
     * @param \ReflectionClass $Class|NULL The class instance or NULL for the current class
     * @return array a list of methods
     * @throws \CPath\BuildException if no class was specified and no default class exists
     */
    public function getHandlerMethods(\ReflectionClass $Class=NULL) {
        if(!$Class) $Class = $this->getCurrentClass();
        $methods = $Class->getConstant('Route_Methods') ?: 'GET|POST|CLI';

        $allowed = explode('|', self::METHODS);
        $methods = explode('|', $methods);
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
     * Gets the default public route path for this handler
     * @param \ReflectionClass $Class|NULL The class instance or NULL for the current class
     * @return string The public route path
     */
    public function getHandlerDefaultPath(\ReflectionClass $Class=NULL) {
        if(!$Class) $Class = $this->getCurrentClass();
        $path = $Class->getConstant('Route_Path');
        if(!$path) $path = '/'.str_replace('\\', '/', strtolower($Class->getName()));
        return $path;
    }

    /**
     * Determines the Handler route(s) from constants or the class name
     * @param \ReflectionClass $Class|NULL The class instance or NULL for the current class
     * @return IRoute[] a list of IRoute instances
     * @throws \CPath\BuildException when a build exception occurred
     */
    public function getHandlerRoutes(\ReflectionClass $Class=NULL) {
        if(!$Class) $Class = $this->getCurrentClass();

        $routes = array();
        if($Class->implementsInterface("CPath\\Interfaces\\IRoutable")) {
            /** @var IRoutable $Routable  */
            $Routable = $Class->newInstance();
            $routes = $Routable->getAllRoutes($this);
        } else {
            $path = $this->getHandlerDefaultPath($Class);
            foreach($this->getHandlerMethods($Class) as $method)
                $routes[] = new Route($method . ' ' . $path, $Class->getName());
        }
        return $routes;
    }

    // Statics

    public static function rebuildAPCCache() {
        $c = 0;
        $cache = apc_cache_info('user');
        if($cache)
            foreach($cache['cache_list'] as $info) {
                if(strpos($info['info'], RouterAPC::APC_PREFIX) === 0) {
                    apc_delete($info['info']);
                    $c++;
                }
            }
        elseif($cache===false) {
            Log::e(__CLASS__, "APC Cache info could not be got. Make sure to set apc.enable_cli=1 in php.ini");
            return;
        }
        Log::v(__CLASS__, "Cleared {$c} APC Route Entries");

        $routes = Router::getRoutes();
        /** @var IRoute $Route */
        foreach($routes as $Route)
            apc_store(RouterAPC::PREFIX_ROUTE.$Route->getPrefix(), $Route);

        Log::v(__CLASS__, "Stored (%s) into APC Cache", sizeof($routes));
    }
}
