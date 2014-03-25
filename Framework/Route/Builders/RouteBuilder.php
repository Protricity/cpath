<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Route\Builders;
use CPath\Base;
use CPath\Compile;
use CPath\Config;
use CPath\Exceptions\BuildException;
use CPath\Framework\Build\API\Build;
use CPath\Framework\Build\IBuilder;
use CPath\Framework\Render\IRenderAggregate;
use CPath\Log;

/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class RouteBuilder implements IBuilder {
    const TMPL_ROUTES = <<<'PHP'
<?php
/** @var \CPath\Routes $Map */
%s
PHP;

    const TMPL_CUSTOM_ROUTES = <<<'PHP'
<?php
// $Map->map('GET /path/to', 'My\Handler');
);
PHP;

    /** @var IRenderAggregate[] */
    private $mRoutes = array();

    protected function __construct() {

    }

    /**
     * @param String $route '[METHOD] [PATH]'
     * @param IRenderAggregate $Destination
     * @throws \CPath\Exceptions\BuildException
     */
    protected function addRoute($route, IRenderAggregate $Destination) {
        list($method, $path) = explode(' ', $route, 2);
        $class = get_class($Destination);
        if(!$path)
            $path = '/' . Base::getClassPath($class, false);
        if($path[0] !== '/')
            $path = '/' . Base::getClassPath($class, false) . '/' . $path;
        $route = $method . ' ' . $path;

        if(isset($this->mRoutes[$route]))
            throw new BuildException("Route Prefix already exists: " . $route . " (" . get_class($this->mRoutes[$route]) . ")");

        $this->mRoutes[$route] = $Destination;
    }

//    private function getCustomRoutes() {
//        $routes = array();
//        $path = Config::getGenPath().'routes.custom.php';
//        if(file_exists($path)) {
//            include $path;
//        } else {
//            file_put_contents($path, self::TMPL_CUSTOM_ROUTES);
//        }
//
//        return $routes;
//    }

    /**
     * Checks to see if any routes were built, and builds them into [gen]/routes.php
     */
    public function buildComplete() {
        if(!$this->mRoutes) {
            Log::e(__CLASS__, "No routes found.");
            return;
        }
        //foreach($this->getCustomRoutes() as $Route)
         //   $this->addRoute($Route);

        // Sort routes
        uksort($this->mRoutes, function ($a, $b){
            return (substr_count($b, '/') - substr_count($a, '/'));
        });

        $max = 0;

        foreach($this->mRoutes as $route => $Buildable) {
            list(, $path) = explode(' ', $route, 2);

            if(($l = strlen($path) > $max))
                $max = $l;
        }


//        $phpUse = '';
//        foreach($useClass as $class=>$alias)
//            $phpUse .= "\nuse " . $class . " as " . $alias . ";";

        $phpRoute = '';
        $c=0;
        foreach($this->mRoutes as $route => $Buildable) {
            list($method, $path) = explode(' ', $route, 2);
            $class = get_class($Buildable);

            if($c > 0)
                $phpRoute .= ' ||';

            $phpRoute .= "\n\$Map->map('{$method} {$path}', '{$class}')";
            $c++;
        }
        $phpRoute .= ';';

        $output = sprintf(self::TMPL_ROUTES, $phpRoute);
        $path = Config::getGenPath().'routes.gen.php';
        if(!is_dir(dirname($path)))
            mkdir(dirname($path), NULL, true);
        file_put_contents($path, $output);
        Log::v(__CLASS__, count($this->mRoutes) . " Route(s) rebuilt.");

//        if(Config::$APCEnabled)
//            self::rebuildAPCCache();

        Compile::$RouteMax = $max;
        Compile::commit();

        $this->mRoutes = array();
    }

    // Statics

//    public static function rebuildAPCCache() {
//        if(Base::isCLI())
//            return;
//        Log::e(__CLASS__, "Rebuilding APC Cache");
//        $c = 0;
//        $cache = apc_cache_info('user');
//        if($cache)
//            foreach($cache['cache_list'] as $info) {
//                if(strpos($info['info'], RouterAPC::PREFIX) === 0) {
//                    apc_delete($info['info']);
//                    $c++;
//                }
//            }
//        elseif($cache===false) {
//            Log::e(__CLASS__, "APC Cache info could not be got. Make sure to set apc.enable_cli=1 in php.ini");
//            return;
//        }
//        Log::v(__CLASS__, "Cleared {$c} APC Entries");
//
//        $routes = Router::getRoutes();
//        /** @var \CPath\Route\IRoute $Route */
//        foreach($routes as $Route)
//            if(true !== apc_store(RouterAPC::PREFIX_ROUTE.$Route->getPrefix(), $Route))
//                throw new BuildException("Router APC failed to store Route: " . $Route->getPrefix());
//
//        Log::v(__CLASS__, "Stored (%s) into APC Cache", sizeof($routes));
//    }

    // Static

    /**
     * @param String $route '[METHOD] [PATH]'
     * @param IRenderAggregate $Destination
     */
    public static function buildRoute($route, IRenderAggregate $Destination) {
        static $Inst = null;
        if(!$Inst) {
            $Inst = new RouteBuilder();
            Build::registerBuilder($Inst);
        }
        $Inst->addRoute($route, $Destination);
    }
}
