<?php
namespace CPath\Framework\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Theme\IFragmentTheme;
use CPath\Request\IRequest;
use CPath\Templates\Themes\CPathDefaultTheme;
use CPath\Render\HTML\HTMLRenderUtil;

abstract class AbstractFormFragment implements IRenderHTML, ISupportHeaders{

    private $mTheme;

    /**
     * @param \CPath\Render\HTML\Theme\Interfaces\\CPath\Render\HTML\Theme\Fragment\\CPath\Render\HTML\Theme\IFragmentTheme $Theme
     */
    public function __construct(IFragmentTheme $Theme = null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $Head->writeScript(__NAMESPACE__ . '/assets/jquery.min.js');

        $Head->writeScript(__NAMESPACE__ . '/assets/cpath.js');

        $Head->writeStyleSheet(__NAMESPACE__ . '/assets/api.css');
        $Head->writeScript(__NAMESPACE__ . '/assets/api.js');

        $Head->writeScript(__NAMESPACE__ . '/assets/form.js');
        $Head->writeStyleSheet(__NAMESPACE__ . '/assets/formfragment.css');
        $Head->writeScript(__NAMESPACE__ . '/assets/formfragment.js');
    }

    /**
     * Render this API Form
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @param \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    abstract function renderForm(IRequest $Request, IAttributes $Attr=NULL);

    public function getTheme() { return $this->mTheme; }

    /**
     * Render request as html and sends headers as necessary
     * @param \CPath\Framework\API\Fragments\IRenderRequest|\CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @internal param $ \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRenderRequest $Request, IAttributes $Attr = null)
    {
        $this->renderForm($Request, $Attr);
    }

    protected function renderFormButtons(IRequest $Request) {
        $Util = new HTMLRenderUtil($Request);
        $Util->button('Submit', 'form-button-submit');
    }

}
