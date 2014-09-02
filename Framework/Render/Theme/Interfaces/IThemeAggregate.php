<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\Theme\Interfaces;


use CPath\Framework\Render\Theme\Interfaces\ITheme;

interface IThemeAggregate {

    /**
     * Set up a view according to this theme
     * @return ITheme
     */
    function loadTheme();
}