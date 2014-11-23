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
use CPath\Request\IRequest;

class HTMLHeaderStyleSheet implements IHTMLSupportHeaders
{
    private $mPath;

    /**
     * Create a <script> header inst
     * @param String $path the stylesheet path
     */
    public function __construct($path) {
        $this->mPath = $path;
    }

    /**
     * Write all support headers used by this IView inst
     * @param \CPath\Request\IRequest $Request
     * @param IHeaderWriter $Head the writer inst to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeStyleSheet($this->mPath);
    }
}