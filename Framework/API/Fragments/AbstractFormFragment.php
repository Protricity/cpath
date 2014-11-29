<?php
namespace CPath\Framework\API\Fragments;

use CPath\Base;
use CPath\Config;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLRenderUtil;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Theme\IFragmentTheme;
use CPath\Request\IRequest;
use CPath\Templates\Themes\CPathDefaultTheme;

abstract class AbstractFormFragment implements IRenderHTML, IHTMLSupportHeaders{

    private $mTheme;

    /**
     * @param \CPath\Render\HTML\Theme\Interfaces\\CPath\Render\HTML\Theme\Fragment\\CPath\Render\HTML\Theme\IFragmentTheme $Theme
     */
    public function __construct(IFragmentTheme $Theme = null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Write all support headers used by this IView inst
     * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
     * @return void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $Head->writeScript(__DIR__ . '/assets/jquery.min.js');

        $Head->writeScript(__DIR__ . '/assets/cpath.js');

        $Head->writeStyleSheet(__DIR__ . '/assets/api.css');
        $Head->writeScript(__DIR__ . '/assets/api.js');

        $Head->writeScript(__DIR__ . '/assets/form.js');
        $Head->writeStyleSheet(__DIR__ . '/assets/formfragment.css');
        $Head->writeScript(__DIR__ . '/assets/formfragment.js');
    }

    /**
     * Render this API Form
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render
     * @param \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    abstract function renderForm(IRequest $Request, IAttributes $Attr=NULL);

    public function getTheme() { return $this->mTheme; }

	/**
	 * Render request as html and sends headers as necessary
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return void
	 */
    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null)
    {
        $this->renderForm($Request, $Attr);
    }

    protected function renderFormButtons(IRequest $Request) {
        $Util = new HTMLRenderUtil($Request);
        $Util->button('Submit', 'form-button-submit');
    }

}
