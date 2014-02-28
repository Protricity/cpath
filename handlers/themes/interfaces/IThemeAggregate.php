<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;


interface IThemeAggregate {

    /**
     * Set up a view according to this theme
     * @return ITheme
     */
    function loadTheme();
}