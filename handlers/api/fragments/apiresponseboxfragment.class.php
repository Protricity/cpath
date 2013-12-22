<?php
namespace CPath\Handlers\API\Fragments;

use CPath\Config;
use CPath\Handlers\Interfaces\IAttributes;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Handlers\Util\Attr;
use CPath\Handlers\Util\HTMLRenderUtil;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\ExceptionResponse;
use CPath\Util;

class APIResponseBoxFragment{
    private $mTheme;

    function __construct(ITheme $Theme=null) {
        $this->mTheme = $Theme;
    }

    function renderResponseBox(IRequest $Request, IResponse $Response=null, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);
        $Util = new HTMLRenderUtil($Request);
        //$Util->button('JSON', 'form-button-submit-json');

        $Attr->addClass('apiview-response');
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
