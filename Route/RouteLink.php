<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 5:15 PM
 */
namespace CPath\Route;

class RouteLink
{
    private $mPrefix, $mTarget;

    public function __construct($prefix, $target) {
        $this->mPrefix = $prefix;
        $this->mTarget = $target;
    }
}