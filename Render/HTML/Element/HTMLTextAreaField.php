<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:38 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Common\HTMLText;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLTextAreaField extends HTMLElement
{
    private $mContent;

    public function __construct($value = null, $attr = null) {
        parent::__construct('textarea', $attr);
        $this->setValue($value);
    }

    public function setValue($value) {
        if ($this->mContent)
            $this->removeContent($this->mContent);

        if (!$value instanceof IRenderHTML)
            $value = new HTMLText($value);

        $this->mContent = $value;
        $this->addContent($value);
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Attr = $this->getAttributes()->merge($Attr);
        echo RI::ni(), "<", $this->getElementType(), $Attr, ">";
        RI::ai(1);

        $this->renderContent($Request);

        RI::ai(-1);
        echo "</", $this->getElementType(), ">";
    }
}