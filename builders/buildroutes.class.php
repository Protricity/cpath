<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\Interfaces\IHandler;
use CPath\BuildException;
use CPath\Base;

/**
 * Class BuildHandlers
 * @package CPath\Builders
 *
 * Provides building methods for Handler Classes.
 * Builds [gen]/routes.php
 */
class BuildRoutes {
    const IHandler = "CPath\\Interfaces\\IHandler";
    private static $mRoutes = array();

    /**
     * Builds a route for the specified IHandler class
     * @param \ReflectionClass $Class
     * @throws \CPath\BuildException
     */
    public static function build(\ReflectionClass $Class) {
        if(!$Class->implementsInterface(self::IHandler))
            throw new BuildException("Class ".$Class->getName()." does not implement ".self::IHandler);

        self::$mRoutes[] = array(
            'match' => static::getHandlerRoute($Class),
            'class' => $Class->getName(),
        );
    }

    /**
     * Checks to see if any routes were built, and builds them into [gen]/routes.php
     * @param \ReflectionClass $Class
     */
    public static function buildComplete(\ReflectionClass $Class) {
        if(!self::$mRoutes)
            return;

        $output = "<?php\n\$routes = array(";
        foreach(self::$mRoutes as $route)
            $output .= "\n\tarray('" . $route['match'] . "', '" . $route['class'] . "'),";
        $output .= "\n);";
        file_put_contents(Base::getGenPath().'routes.php', $output);
        Base::log(count(self::$mRoutes) . " Route(s) rebuilt.");

        self::$mRoutes = array();
    }

    /**
     * Determines the Handler route from constants or the class name
     * @param \ReflectionClass $Class
     * @return string
     */
    public static function getHandlerRoute(\ReflectionClass $Class) {
        $method = $Class->getConstant('ROUTE_METHOD') ?: 'GET';
        $route = $Class->getConstant('ROUTE_PATH');
        if(!$route) {
            $route = '/'.str_replace('\\', '/', $Class->getName());
        }
        $route = strtolower($route);
        return strtoupper($method) . ' ' . $route;
    }
}
