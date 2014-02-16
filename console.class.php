<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Framework\Render\Interfaces\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Request\Types\CLI;
use CPath\Interfaces\IBuildable;
use CPath\Route\Router;

class Console implements IRender { // Broke }, IBuildable {

    const ROUTE_PATH = '/console';     // Allow manual building from command line: 'php index.php build'
    const ROUTE_METHODS = 'CLI';    // CLI only

    function render(IRequest $Request)
    {
        $routes = array();
        foreach(Router::getRoutes() as $Route){
            /** @var \CPath\Route\IRoute $Route */
            list($method, $route) = explode(' ', $Route->getPrefix(), 2);
            if(!isset($routes[$route]))
                $routes[$route] = array();
            $routes[$route][] = $method;
        }

        $history = array();
        $ns = '/';
        echo "CPath Console Mode (exit, ls, cd, history, !!)\n";
        while(true) {
            echo $ns, '>';
            $cmd=trim(fgets(STDIN));
            if($history && strpos($cmd, '!!') !== false)
                $cmd = str_replace('!!', $history[sizeof($history)-1], $cmd);
            $history[] = $cmd;
            $args = explode(' ', $cmd);
            switch(trim(strtolower($args[0]))) {
                case 'exit':
                    break 2;
                case 'ls':
                    foreach($routes as $route=>$methods)
                        echo $route, ' (',implode(', ',$methods),')',"\n";
                    break;
                case 'history':
                    foreach($history as $h)
                        echo $h,"\n";
                    break;
                case 'cd':
                    if(!isset($args[1])) {
                        $ns = '/';
                        break;
                    }
                    $arg = trim($args[1]);
                    if($arg == '..') {
                        $ns = dirname($ns) . '/';
                        if($ns[0] == '\\') $ns = substr($ns, 1);
                        break;
                    }
                    $dir = $ns . $arg . '/';
                    foreach($routes as $route=>$method) {
                        if(strpos($route, $dir) === 0) {
                            $ns = $dir;
                            break 2;
                        }
                        if(strpos($route, $arg) === 0) {
                            $ns = $route;
                            break 2;
                        }
                    }
                    echo "Namespace '{$dir} was not found\n";
                    break;
                default:
                    if(!isset($args[0]) || !($arg = trim($args[0])))
                        break;
                    if($arg[0] != '/')
                        $arg = $ns . $arg;
                    $args[0] = $arg;
                    $Cli = CLI::fromArgs($args);
                    try{
                        $Cli->findRoute()
                            ->loadHandler()
                            ->render($Cli);
                    } catch (\Exception $ex) {
                        echo "Exception: ",$ex->getMessage(),"\n",$ex->getFile(),":",$ex->getLine(),"\n";
                    }
            }
        }
    }

    /**
     * Return an instance of the class for building purposes
     * @return IBuildable|NULL an instance of the class or NULL to ignore
     */
    static function createBuildableInstance() {
        return new static();
    }
}