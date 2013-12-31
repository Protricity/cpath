<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Base;
use CPath\Compile;
use CPath\Config;
use CPath\Constructable\Constructable;
use CPath\Exceptions\BuildException;
use CPath\Interfaces\IBuildable;
use CPath\Interfaces\IHandler;
use CPath\Log;

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
use CPath\Route\Route;
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
        if(!$Buildable instanceof IRoutable)
            return false;
        $this->mCurrentClass = $Buildable;
        $Route = $Buildable->loadRoute();
        echo get_class($Route);
        if(!$Route)
            Log::e(__CLASS__, "Invalid Route returned for " . get_class($Buildable));
        else
            $this->addRoute($Route);
        return true;
    }

//    private function getCurrentClass() {
//        if(!$this->mCurrentClass)
//            throw new BuildException("No current class available");
//        return $this->mCurrentClass;
//    }

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
     * Add an IRoute to the Route Builder
     * @param IRoute $Route the route path or null for default
     * @param bool $replace
     * @return void
     * @throws BuildException
     */
    function addRoute(IRoute $Route, $replace=false) {
        if(!$replace && isset($this->mRoutes[$Route->getPrefix()])) {
            throw new BuildException("Route Prefix already exists: ".$Route->getPrefix());
        }
        $this->mRoutes[$Route->getPrefix()] = $Route;
    }

    /**
     * @return IRoute[] array of IRoute instances
     */
    function getRoutes() {
        return array_values($this->mRoutes);
    }

    /**
     * Checks to see if any routes were built, and builds them into [gen]/routes.php
     */
    public function buildComplete() {
        if(!$this->mRoutes)
            return;
        /** @var \CPath\Route\IRoute $Route */
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

        $defaultClass = get_class(new Route('', '')); // TODO: hack
        foreach($this->mRoutes as $Route) {
            $class = get_class($Route); // $defaultClass;
            if(!isset($useClass[$class]))
                $useClass[$class] = 'Route' . ($i++ ?: '');
            if(($l = strlen($Route->getPrefix())) > $max)
                $max = $l;
        }


        $phpUse = '';
        foreach($useClass as $class=>$alias)
            $phpUse .= "\nuse " . $class . " as " . $alias . ";";
        $phpRoute = '';
        foreach($this->mRoutes as $Route) {
            $constName = $useClass[get_class($Route)]; // $useClass[$defaultClass]; // get_class($Route)];
            //$args = '';
            //$i=0;
            //foreach($Route->getExportArgs() as $arg)
            //    $args .= ($i++ ? ', ' : '') . var_export($arg, true);
            //$phpRoute .= "\n\tnew " . $useClass[get_class($Route)] . '(' . $args . '),';
            $phpRoute .= "\n\t" . Constructable::exportToPHPCode($Route, $constName) . ',';
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
        /** @var \CPath\Route\IRoute $Route */
        foreach($routes as $Route)
            apc_store(RouterAPC::PREFIX_ROUTE.$Route->getPrefix(), $Route);

        Log::v(__CLASS__, "Stored (%s) into APC Cache", sizeof($routes));
    }

    public static function createBuildableInstance() {
        return new static;
    }
}
