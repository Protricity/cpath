<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IArrayObject extends \ArrayAccess, \IteratorAggregate, \Countable {

    /**
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getData(0, 'key') gets $data[0]['key'];
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException
     */
    function &getDataPath($_path=NULL);
}
