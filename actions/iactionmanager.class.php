<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Actions;

use CPath\Interfaces\IViewConfig;

interface IActionManager extends IViewConfig {

    /**
     * Add an action to the available list
     * @param IActionable $Action
     * @return mixed
     */
    function addAction(IActionable $Action);

    /**
     * Return all available actions
     * @return IActionable[]
     */
    function getActions();
}