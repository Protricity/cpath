<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 12:47 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLHeaderScript implements IRenderHTML, IHTMLSupportHeaders
{
    private $mPath, $mDefer, $mCharset;

    /**
     * Create a <script> header instance
     * @param String $path the script path
     * @param bool $defer
     * @param null $charset
     */
    public function __construct($path, $defer = false, $charset = null) {
        $this->mPath = $path;
        $this->mDefer = $defer;
        $this->mCharset = $charset;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        // No render for Headers
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeScript($this->mPath, $this->mDefer, $this->mCharset);
    }
}

