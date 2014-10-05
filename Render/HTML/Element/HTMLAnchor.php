<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:58 PM
 */
namespace CPath\Render\HTML\Element;

class HTMLAnchor extends HTMLElement
{
	const ALLOW_CLOSED_TAG = false;
	const TRIM_CONTENT = true;

	/**
	 * @param string $href
	 * @param string|null $text
	 * @param string|\CPath\Render\HTML\Attribute\IAttributes $attr
	 */
    public function __construct($href, $text=null, $attr=null) {
        parent::__construct('a', $attr, $text);
        $this->setAttribute('href', $href);
    }
}