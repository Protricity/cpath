<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/4/14
 * Time: 6:40 PM
 */
namespace CPath\Handlers\HTML\Layouts;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\Attr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class ThreeSectionLayout implements IRenderHTML, IHTMLSupportHeaders
{
    const SECTION_HEADER = 'header';
    const SECTION_BODY = 'body';
    const SECTION_FOOTER = 'footer';

    /** @var \CPath\Render\HTML\Element\HTMLElement */
    private $mHeader;
    /** @var \CPath\Render\HTML\Element\HTMLElement */
    private $mBody;
    /** @var \CPath\Render\HTML\Element\HTMLElement */
    private $mFooter;

    public function __construct(IRenderHTML $_Content=null) {
        $this->mHeader = new HTMLElement('div', new Attr(static::SECTION_HEADER));
        $this->mBody = new HTMLElement('div', new Attr(static::SECTION_BODY));
        $this->mFooter = new HTMLElement('div', new Attr(static::SECTION_FOOTER));

        foreach(func_get_args() as $arg)
            if($arg)
                $this->addContent($arg);
    }

    public function getHeader() { return $this->mHeader; }
    public function getBody()   { return $this->mBody; }
    public function getFooter() { return $this->mFooter; }

    /**
     * Add HTML Container Content
     * @param IRenderHTML $Content
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content) {
        $this->mBody->addContent($Content);
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Request\IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeStyleSheet(__NAMESPACE__ . '\assets\three-section-page.css');

        $this->mHeader->writeHeaders($Request, $Head);
        $this->mBody->writeHeaders($Request, $Head);
        $this->mFooter->writeHeaders($Request, $Head);
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $this->mHeader->renderHTML($Request);
        $this->mBody->renderHTML($Request, $Attr);
        $this->mFooter->renderHTML($Request);
    }
}