<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\Theme\Interfaces;

use CPath\Framework\Render\Theme\Interfaces\IFragmentTheme;
use CPath\Framework\Render\Theme\Interfaces\IPageTheme;
use CPath\Framework\Render\Theme\Interfaces\ISearchTheme;
use CPath\Framework\Render\Theme\Interfaces\ITableTheme;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Theme\Interfaces\IBrowseTheme;

interface ITheme extends ITableTheme, IFragmentTheme, IPageTheme, ISearchTheme, IBrowseTheme{

    /**
     * TODO: is this a good idea?
     * Render support head elements as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderHeaderHTML(IRequest $Request);
}