<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\CLI\Option\Interfaces;

interface IOptionProcessor {

    /**
     * Process an option map using this map
     * @param IOptionMap $Map
     * @return void
     */
    function processMap(IOptionMap $Map);
}