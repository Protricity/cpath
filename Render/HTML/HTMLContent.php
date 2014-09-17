<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 7:01 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;

class HTMLContent implements IRenderHTML
{
    private $mHTML;

    public function __construct($html) {
        $this->mHTML = $html;
    }

    /**
     * Render request as html
     * @param \CPath\Render\HTML\IRenderRequest|\CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @internal param $ \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRenderRequest $Request, IAttributes $Attr = null) {
        echo RI::ni(), $this->mHTML;
    }
}

