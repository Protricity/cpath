<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\View\Theme\Interfaces;

use CPath\Framework\View\Theme\Interfaces\IBrowseTheme;
use CPath\Framework\View\Theme\Interfaces\IFragmentTheme;
use CPath\Framework\View\Theme\Interfaces\IPageTheme;
use CPath\Framework\View\Theme\Interfaces\ISearchTheme;
use CPath\Framework\View\Theme\Interfaces\ITableTheme;
use CPath\Interfaces\IViewConfig;

interface ITheme extends IViewConfig, ITableTheme, IFragmentTheme, IPageTheme, ISearchTheme, IBrowseTheme{
}