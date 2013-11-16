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
     * Returns an IHandler instance to represent this class
     * Note: This method must be STATELESS should NOT be affected by outside factors such as user session
     * @return IHandler $Handler an IHandler instance
     */
    function getAggregateHandler();
}