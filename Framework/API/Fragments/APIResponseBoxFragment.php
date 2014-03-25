<?php
namespace CPath\Framework\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Util\ResponseUtil;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

class APIResponseBoxFragment implements IViewConfig{
    private $mTheme;

    function __construct(ITheme $Theme=null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }
    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        $basePath = Base::getClassPath($this, true);
        $View->addHeadStyleSheet($basePath . 'assets/apiresponseboxfragment.css', true);
        $View->addHeadScript($basePath . 'assets/apiresponseboxfragment.js', true);
    }

    function renderResponseBox(IRequest $Request, IResponse $Response=null, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);
        // = new HTMLRenderUtil($Request);
        //$Util->button('JSON', 'form-button-submit-json');

        $Attr->addClass('apiresponsebox-fragment');
        $Attr->addStyle('display: none');

        $Theme = $this->mTheme;
        $Theme->renderFragmentStart($Request, "Ajax Info", $Attr);
        $Theme->renderFragmentStart($Request, "DataResponse", Attr::get('response-content'));
        if($Response) {
            $Util = new ResponseUtil($Response);
            $Util->renderJSON($Request);
        }
        $Theme->renderFragmentEnd($Request);
        $Theme->renderFragmentStart($Request, "DataResponse Headers", new Attr('response-headers'));
        $Theme->renderFragmentEnd($Request);
        $Theme->renderFragmentStart($Request, "Request Headers", new Attr('request-headers'));
        $Theme->renderFragmentEnd($Request);
        $Theme->renderFragmentEnd($Request);
    }
}
