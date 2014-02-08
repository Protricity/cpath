<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Compare\IComparable;
use CPath\Compare\IComparator;
use CPath\Compare\NotEqualException;
use CPath\Framework\Response\Interfaces\IResponseCode;

/**
 * Class MissingRoute - a placeholder for missing routes
 * @package CPath
 */
class MissingRoute implements IRoute{
    private $mRoutePath;
    public function __construct($routePath) {
        $this->mRoutePath = $routePath;
    }

    public function loadHandler() {
        throw new NoRoutesFoundException("No Routes Matched " . $this->mRoutePath, IResponseCode::STATUS_NOT_FOUND);
    }


    function getPrefix() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function getDestination() { throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found"); }
    function exportConstructorArgs() { throw new InvalidRouteException("File request was passed to Script"); }
    protected function &getArray() { throw new InvalidRouteException("File request was passed to Script"); }

    /**
     * Try's a route against a request path and parse out any request args
     * @param string|null $requestPath the request path to match
     * @param Array &$args populated with args parsed out of the path
     * @return boolean return true if match is found
     * @throws NoRoutesFoundException
     */
    function match($requestPath, Array &$args = array()) {
        throw new NoRoutesFoundException("Route '{$this->mRoutePath}' was not found");
    }

    /**
     * Compare two objects
     * @param IComparable $obj the object to compare against $this
     * @param IComparator $C the IComparator instance
     * @return integer < 0 if $this is less than $obj; > 0 if $this is greater than $obj, and 0 if they are equal.
     * @throws NotEqualException if the objects were not equal
     */
    function compareTo(IComparable $obj, IComparator $C)
    {
        throw new NotEqualException("Route '{$this->mRoutePath}' was not found");
    }
}