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
    /**
     * @param string $href
     * @param String|\CPath\Render\HTML\Attribute\IAttributes $attr
     */
    public function __construct($href, $attr = null) {
        parent::__construct('a', $attr);
        $this->setAttribute('href', $href);
    }
}