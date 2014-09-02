<?php
namespace CPath\Framework\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\Header\Interfaces\IHeaderWriter;
use CPath\Framework\Render\Header\Interfaces\ISupportHeaders;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\IContainerDEL;
use CPath\Framework\Render\Theme\CPathDefaultTheme;
use CPath\Framework\Render\Theme\Interfaces\ITableTheme;
use CPath\Framework\Render\Util\HTMLRenderUtil;
use CPath\Interfaces\IViewConfig;

abstract class AbstractFormFragment implements IRenderHTML, ISupportHeaders{

    private $mTheme;

    /**
     * @param ITableTheme $Theme
     * @internal param \CPath\Framework\API\Interfaces\IAPI $API
     */
    public function __construct(ITableTheme $Theme = null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Framework\Render\Header\Interfaces\IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $basePath = Base::getClassPath($this, true);

        $Head->writeScript($basePath . 'assets/jquery.min.js');

        $Head->writeScript($basePath . 'assets/cpath.js');

        $Head->writeStyleSheet($basePath . 'assets/api.css');
        $Head->writeScript($basePath . 'assets/api.js');

        $Head->writeScript($basePath . 'assets/form.js');
        $Head->writeStyleSheet($basePath . 'assets/formfragment.css');
        $Head->writeScript($basePath . 'assets/formfragment.js');
    }

    /**
     * Render this API Form
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    abstract function renderForm(IRequest $Request, IAttributes $Attr=NULL);

    public function getTheme() { return $this->mTheme; }

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        $this->renderForm($Request, $Attr);
    }

    protected function renderFormButtons(IRequest $Request) {
        $Util = new HTMLRenderUtil($Request);
        $Util->button('Submit', 'form-button-submit');
    }

}
