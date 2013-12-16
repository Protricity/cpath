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
use CPath\Helpers\Describable;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Model\DB\SearchResponse;


class SearchFormUtil implements IDescribable {
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

    function getQuery() {
        return $this->mResponse->getQuery();
    }

    public function addHeadElementsTo(IView $View) {
        $this->mAPIView->mergeHeadElementsInto($View);
        $basePath = Base::getClassPublicPath($this, false);
        $View->addHeadStyleSheet($basePath . 'assets/searchformutil.css');
        $View->addHeadScript($basePath . 'assets/searchformutil.js');
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