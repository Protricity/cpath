<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;
use CPath\Builders\RouteBuilder;
use CPath\Util;

/**
 * Class Route - a route entry
 * @package CPath
 */
class MissingRoute extends Route{
    private $mRoutePath;
    public function __construct($routePath) {
        $this->mRoutePath = $routePath;
    }

    /**
     * Renders the route destination
     * @param array $request optional request parameters
     * @return void
     */
    public function render(Array $request=NULL) {
        header("HTTP/1.0 404 Route not found");
        print("No Routes Matched: " . $this->mRoutePath);
    }

}