<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Util;

use CPath\Base;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Views\APIView;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IViewConfig;
use CPath\Model\DB\SearchResponse;


class SearchFormUtil implements IDescribable, IViewConfig {
    private $mTheme, $mAPIView, $mResponse, $mAPI, $mDescriptor, $mDescribable;

    public function __construct(SearchResponse $Response, ITheme $Theme=null) {
        $Query = $Response->getQuery();
        $this->mDescriptor = $Query->getDescriptor();
        $this->mAPI = $this->mDescriptor->getAPI();
        $this->mTheme = $Theme;
        $this->mResponse = $Response;
        $this->mAPIView = new APIView($this->mAPI, null, null, $Theme);
        $this->mDescribable = Describable::get($this->mAPI);
    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        $this->mAPIView->addHeadElementsToView($View);
        $basePath = Base::getClassPublicPath($this, false);
        $View->addHeadStyleSheet($basePath . 'assets/searchformutil.css', true);
        $View->addHeadScript($basePath . 'assets/searchformutil.js', true);
    }

    function getQuery() {
        return $this->mResponse->getQuery();
    }

    public function renderForm(IRequest $Request) {
        $this->mTheme->renderFragmentStart($Request, $this, 'search-form-util');
            $this->mAPIView->renderForm($Request);
            $this->mTheme->renderSearchContent($Request, $this->mResponse, 'search-content');
            $this->mAPIView->renderDebugBox($Request);
        $this->mTheme->renderFragmentEnd($Request);
    }

    /**
     * Get the Object Title
     * @return String description for this Object
     */
    function getTitle() { return $this->mDescribable->getTitle(); }

    /**
     * Get the Object Description
     * @return String description for this Object
     */
    function getDescription() { return $this->mDescribable->getDescription(); }


    /**
     * Implement __toString
     * @return String simple description for this Object
     */
    function __toString() { return (String)$this->mDescribable; }

}