<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:36 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Common\HTMLText;
use CPath\Render\HTML\IRenderHTML;

class HTMLLabel extends HTMLElement
{
    /**
     * @param string $text
     * @param String|\CPath\Render\HTML\Attribute\IAttributes $attr
     */
    public function __construct($text, $attr = null) {
        parent::__construct('label', $attr);
        $this->addContent(new HTMLText($text));
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML|string $Content
     * @param null $key
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content, $key = null) {
        parent::addContent($Content, $key);
        if ($Content instanceof HTMLElement)
            if ($Content->hasAttribute('name'))
                $this->setAttribute('for', $Content->getAttribute('name'));
    }
}


