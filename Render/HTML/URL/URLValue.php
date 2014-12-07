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
use CPath\Render\HTML\Element\HTMLAnchor;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class URLValue implements IRenderHTML, IHasURL
{
    private $mURL;
    private $mContent;

    /**
     * @param String $url
     * @param null $text
     */
    public function __construct($url, $text = null) {
        $this->mURL = $url;
	    if(!$text)
		    $text = parse_url($url, PHP_URL_PATH);
        $this->mContent = $text;
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
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param Attribute\IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
        $url = $Request->getDomainPath() . $this->mURL;
        $Anchor = new HTMLAnchor($url);
        if ($this->mContent)
            $Anchor->setContent($this->mContent);

        $Anchor->renderHTML($Request, $Attr, $Parent);
    }

    function __toString() {
        return $this->mURL;
    }
}