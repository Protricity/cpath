<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:33 AM
 */
namespace CPath\Render\HTML\Common;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLText implements IRenderHTML
{
    private $mText;

    public function __construct($text=null) {
        $this->mText = $text;
    }

    public function setText($text) {
        $this->mText = $text;
    }

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param Attribute\IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
        if($this->mText !== null)
            echo $this->mText;
    }
}

