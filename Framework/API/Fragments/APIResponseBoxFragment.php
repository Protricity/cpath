<?php
namespace CPath\Framework\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Render\HTML\Attribute\Attr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Framework\View\IContainerDEL;
use CPath\Templates\Themes\CPathDefaultTheme;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

class APIResponseBoxFragment implements IRenderHTML, ISupportHeaders{
    private $mTheme;
    private $mResponse;

    function __construct(IResponse $Response=null, ITheme $Theme=null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
        $this->mResponse = $Response;
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Framework\Render\Header\Interfaces\\CPath\Framework\Render\Header\IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $basePath = Base::getClassPath($this, true);
        $Head->writeScript($basePath . 'assets/vkbeautify.min.js');

        $Head->writeStyleSheet($basePath . 'assets/apiresponseboxfragment.css', true);
        $Head->writeScript($basePath . 'assets/apiresponseboxfragment.js', true);


    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Attr = Attr::fromClass($Attr);
        // = new HTMLRenderUtil($Request);
        //$Util->button('JSON', 'form-button-submit-json');

        $Attr->addClass('apiresponsebox-fragment');
        $Attr->addStyle('display: none');

        $Theme = $this->mTheme;
        $Theme->renderFragmentStart($Request, "Ajax Info", $Attr);
        $Theme->renderFragmentStart($Request, "DataResponse", Attr::fromClass('response-content'));
        if($this->mResponse) {
            $Util = new ResponseUtil($this->mResponse);
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
