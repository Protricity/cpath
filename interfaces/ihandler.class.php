<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IHandler {
    //const ROUTE_METHODS = 'GET|POST|...';
    //const ROUTE_PATH = NULL; //'/my/api/';

    function render(Array $args);
}