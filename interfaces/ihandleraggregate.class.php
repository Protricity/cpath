<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IHandlerAggregate {

    /**
     * Return a handler to act in place of this handler
     * @return IHandler $Handler an IHandler instance
     */
    function getHandler();
}