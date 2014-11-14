<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 12:50 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLHeaderStyleSheet implements IRenderHTML, IHTMLSupportHeaders
{
    private $mPath;

    /**
     * Create a <script> header instance
     * @param String $path the stylesheet path
     */
    public function __construct($path) {
        $this->mPath = $path;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @internal param $ \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        // No render for Headers
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Request\IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeStyleSheet($this->mPath);
    }
}