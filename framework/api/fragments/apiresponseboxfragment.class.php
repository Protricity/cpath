<?php
namespace CPath\Handlers\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Handlers\Interfaces\IAttributes;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Util\Attr;
use CPath\Handlers\Util\HTMLRenderUtil;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Interfaces\IViewConfig;
use CPath\Framework\Response\Types\ExceptionResponse;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Util;

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
        $basePath = Base::getClassPublicPath($this, false);
        $View->addHeadStyleSheet($basePath . 'assets/apiresponseboxfragment.css', true);
        $View->addHeadScript($basePath . 'assets/apiresponseboxfragment.js', true);
    }

    function renderResponseBox(IRequest $Request, IResponse $Response=null, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);
        $Util = new HTMLRenderUtil($Request);
        //$Util->button('JSON', 'form-button-submit-json');

        $Attr->addClass('apiresponsebox-fragment');
        $Attr->addStyle('display: none');

        $Theme = $this->mTheme;
        $Theme->renderFragmentStart($Request, "Ajax Info", $Attr);
        $Theme->renderFragmentStart($Request, "Response", Attr::get('response-content'));
        if($Response) {
            try{
                $JSON = Util::toJSON($Response);
                echo json_encode($JSON);
            } catch (\Exception $ex) {
                $Response = new ExceptionResponse($ex);
                $JSON = Util::toJSON($Response);
                echo json_encode($JSON);
            }
        }
        $Theme->renderFragmentEnd($Request);
        $Theme->renderFragmentStart($Request, "Response Headers", new Attr('response-headers'));
        $Theme->renderFragmentEnd($Request);
        $Theme->renderFragmentStart($Request, "Request Headers", new Attr('request-headers'));
        $Theme->renderFragmentEnd($Request);
        $Theme->renderFragmentEnd($Request);
    }
}
