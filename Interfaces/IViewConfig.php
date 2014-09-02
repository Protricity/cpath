<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

use CPath\Framework\View\IContainerDEL;

interface IViewConfig{

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IContainerDEL $View
     */
    function addHeadElementsToView(IContainerDEL $View);
}
