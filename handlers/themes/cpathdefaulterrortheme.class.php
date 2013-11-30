<?php
namespace CPath\Handlers\Themes;

use CPath\Base;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Helpers\Describable;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;


class CPathDefaultErrorTheme extends CPathDefaultTheme {

    /**
     * Set up a view according to this theme
     * @param IView $View
     * @return mixed
     */
    function setupView(IView $View)
    {
        parent::setupView($View);
        $basePath = Base::getClassPublicPath(__CLASS__, false);
        $View->addHeadStyleSheet($basePath . 'assets/cpathdefaulterrortheme.css');
    }
}

