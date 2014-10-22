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
     * @param String|\CPath\Render\HTML\Attribute\IAttributes $classList
     */
    public function __construct($text=null, $classList = null) {
        parent::__construct('label', $classList);
	    if($text)
	        $this->addContent(new HTMLText($text));
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML|string $Render
     * @param null $key
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Render, $key = null) {
        parent::addContent($Render, $key);
        if ($Render instanceof HTMLElement)
            if ($Render->hasAttribute('name'))
                $this->setAttribute('for', $Render->getAttribute('name'));
    }
}


