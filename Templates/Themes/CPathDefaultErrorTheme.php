<?php
namespace CPath\Templates\Themes;

use CPath\Base;
use CPath\Framework\View\IContainerDEL;


class CPathDefaultErrorTheme extends CPathDefaultTheme {

    /**
     * Set up a view according to this theme
     * @param IContainerDEL $View
     * @return mixed
     */
    function addHeadElementsToView(IContainerDEL $View)
    {
        parent::addHeadElementsToView($View);
        $basePath = Base::getClassPath(__CLASS__, true);
        $Head->writeStyleSheet($basePath . 'assets/cpathdefaulterrortheme.css');
    }
}

