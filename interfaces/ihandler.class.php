<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IHandler {
    //const Route_Methods = 'GET|POST|...';
    //const Route_Path = NULL; //'/my/api/';

    function render(IRoute $Route);
}