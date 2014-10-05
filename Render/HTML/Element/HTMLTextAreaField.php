<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:38 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Common\HTMLText;

class HTMLTextAreaField extends HTMLElement
{
	const ALLOW_CLOSED_TAG = false;
	const TRIM_CONTENT = true;

    public function __construct($text = null, $attr = null) {
        parent::__construct('textarea', $attr);
	    if($text)
            $this->addText($text);
    }

    public function addText($text) {
        $this->addContent(new HTMLText($text));
    }

}