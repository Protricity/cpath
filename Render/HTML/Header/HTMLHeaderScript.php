<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/6/14
 * Time: 12:47 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Request\IRequest;

class HTMLHeaderScript implements IHTMLSupportHeaders
{
    private $mPath, $mDefer, $mCharset;

    /**
     * Create a <script> header inst
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
     * Write all support headers used by this IView inst
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer inst to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeScript($this->mPath, $this->mDefer, $this->mCharset);
    }
}

