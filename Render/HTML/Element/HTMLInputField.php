<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:10 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute;

class HTMLInputField extends HTMLElement
{
    public function __construct($value = null, $type = null, $attr = null) {
        parent::__construct('input', $attr);
        if($value)
            $this->setValue($value);
        if($type)
            $this->setAttribute('type', $type);
    }

    public function setValue($value) {
        $this->setAttribute('value', $value);
    }

    public function setName($value) {
        $this->setAttribute('name', $value);
    }

    public function setID($value) {
        $this->setAttribute('id', $value);
    }
}