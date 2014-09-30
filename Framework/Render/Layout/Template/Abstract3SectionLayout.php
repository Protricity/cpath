<?php
namespace CPath\Framework\Render\Layout\Template;

use CPath\Render\HTML\Attribute\Attr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\WriteOnceHeaderRenderer;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Layout\ContentLayout;
use CPath\Handlers\HTML\Navigation\AbstractNavigator;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Handlers\HTML\Navigation\OrderedListNavigator;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Templates\Themes\CPathDefaultTheme;
use CPath\Render\HTML\Theme\Interfaces\IPageTheme;
use CPath\Request\IRequest;

class Empty3SectionLayout extends Abstract3SectionLayout {

    /**
     * Lazy load page content with request
     * @param \CPath\Request\IRequest $Request the request instance
     * @return void
     */
    protected function populateLayoutContent(IRequest $Request) {
        // TODO: Implement populateLayoutContent() method.
    }
}


abstract class Abstract3SectionLayout extends ContentLayout {

    const SECTION_HEADER = 'header';
    const SECTION_BODY = 'body';
    const SECTION_FOOTER = 'footer';

    /** @var \CPath\Render\HTML\\CPath\Render\HTML\Elements\HTMLElement */
    private $mHeader;
    /** @var \CPath\Render\HTML\Element\HTMLElement */
    private $mBody;
    /** @var \CPath\Render\HTML\Container\\CPath\Render\HTML\Elements\HTMLElement */
    private $mFooter;

    /** @var IPageTheme */
    private $mTheme;

    /** @var \CPath\Handlers\HTML\Navigation\AbstractNavigator */
    private $mNavBar = null;

    public function __construct(IPageTheme $Theme=null, $withNavBar=true) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
        if($withNavBar)
            $this->mNavBar = new OrderedListNavigator();
    }

    /**
     * Lazy load page content with request
     * @param IRequest $Request the request instance
     * @return void
     */
    abstract protected function populateLayoutContent(IRequest $Request);

    /**
     * Render HTML header html
     * @param \CPath\Request\IRequest $Request
     * @return \CPath\Framework\Render\Header\WriteOnceHeaderRenderer
     */
    protected function renderHTMLHeaders(IRequest $Request) {
        $Writer = parent::renderHTMLHeaders($Request);
        $Writer->writeStyleSheet(__NAMESPACE__ . '\assets\-section-page.css');

        if($this->mTheme instanceof IHTMLSupportHeaders)
            $this->mTheme->writeHeaders($Writer);

        return $Writer;
    }

    /**
     * Lazy load page content with request
     * @param \CPath\Request\IRequest $Request the request instance
     * @return void
     */
    protected function loadLayoutContent(IRequest $Request) {
        $this->mHeader = new HTMLElement('div', new Attr(static::SECTION_HEADER));
        $this->mBody = new HTMLElement('div', new Attr(static::SECTION_BODY));
        $this->mFooter = new HTMLElement('div', new Attr(static::SECTION_FOOTER));

        if($this->mNavBar)
            $this->mBody->addContent($this->mNavBar);

        $this->addContent($this->mHeader, static::SECTION_HEADER);
        $this->addContent($this->mBody, static::SECTION_BODY);
        $this->addContent($this->mFooter, static::SECTION_FOOTER);

        $this->populateLayoutContent($Request);
    }

    public function getHeader() { return $this->mHeader; }
    public function getBody() { return $this->mBody; }
    public function getFooter() { return $this->mFooter; }
    public function getNavBar() { return $this->mNavBar; }

}

