<?php
namespace CPath\Handlers\Themes;

use CPath\Base;
use CPath\Handlers\Interfaces\IView;


class CPathDefaultErrorTheme extends CPathDefaultTheme {

    /**
     * Set up a view according to this theme
     * @param IView $View
     * @return mixed
     */
    function addHeadElementsToView(IView $View)
    {
        parent::addHeadElementsToView($View);
        $basePath = Base::getClassPublicPath(__CLASS__, false);
        $View->addHeadStyleSheet($basePath . 'assets/cpathdefaulterrortheme.css');
    }
}

