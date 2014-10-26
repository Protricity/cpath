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

    public function __construct($text = null, $classList = null) {
        parent::__construct('textarea', $classList);
	    if($text)
            $this->addText($text);
    }

	public function setRows($rowCount) {
		$this->setAttribute('rows', $rowCount);
	}

    public function addText($text) {
        $this->addContent(new HTMLText($text));
    }

}