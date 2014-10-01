<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 1:09 PM
 */
namespace CPath\Render\HTML\URL;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Common\HTMLText;
use CPath\Render\HTML\Element\HTMLAnchor;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class URLValue implements IRenderHTML, IHasURL
{
    private $mURL;
    private $mContent;

    /**
     * @param String $url
     * @param null $content
     */
    public function __construct($url, $content = null) {
        $this->mURL = $url;
        $this->mContent = $content;
    }


    /**
     * Return the url for this object
     * @internal param \CPath\Request\IRequest $Request
     * @return String
     */
    function getURL() {
        return $this->mURL;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $url = $Request->getDomainPath() . $this->mURL;
        $Anchor = new HTMLAnchor($url);
        if ($this->mContent) {
            $Content = $this->mContent;
            if (!$Content instanceof IRenderHTML)
                $Content = new HTMLText($Content);
            $Anchor->addContent($Content);
        }
        $Anchor->renderHTML($Request, $Attr);
    }

    function __toString() {
        return $this->mURL;
    }
}