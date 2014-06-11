<?php
namespace CPath\Framework\View\Theme;

use CPath\Base;
use CPath\Framework\View\Theme\CPathDefaultTheme;
use CPath\Framework\View\IView;


class CPathDefaultErrorTheme extends CPathDefaultTheme {

    /**
     * Set up a view according to this theme
     * @param IView $View
     * @return mixed
     */
    function addHeadElementsToView(IView $View)
    {
        parent::addHeadElementsToView($View);
        $basePath = Base::getClassPath(__CLASS__, true);
        $View->addHeadStyleSheet($basePath . 'assets/cpathdefaulterrortheme.css');
    }
}

