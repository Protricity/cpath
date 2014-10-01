<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML;

use CPath\Data\Map\IMappableKeys;
use CPath\Render\HTML\Attribute\Attr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Util\RenderIndents as RI;

class HTMLRenderUtil {

    /**
     * @param $nodeType
     * @param null|string|IAttributes $attr optional attributes for this element
     * @return null|String always returns null
     */
    private function renderElementContent($nodeType, $attr=null) {
        echo $nodeType;
        
        if(is_string($attr)) {
            echo RI::ni(), $attr;
        } elseif($attr instanceof IAttributes) {
            $attr->render();
        }
    }

    /**
     * @param $nodeType
     * @param null|string|IAttributes $attr optional attributes for this element
     */
    function render($nodeType, $attr=null) {
        echo RI::ni(), "<", $this->renderElementContent($nodeType, $attr), "/>";
    }

    /**
     * @param $nodeType
     * @param null|string|IAttributes $attr optional attributes for this element
     */
    function open($nodeType, $attr=null) {
        echo RI::ni(), "<", $this->renderElementContent($nodeType, $attr), ">";
        RI::ai(1);
    }

    function close($nodeType) {
        RI::ai(-1);
        echo RI::ni(), '</', $nodeType, ">";
    }

    /**
     * @param null|string|IAttributes $attr optional attributes for this element
     */
    function formOpen($attr=NULL) {
        $this->open('form', $attr);
    }

    function formClose() {
        $this->close('form');
    }

    /**
     * @param $value
     * @param $type
     * @param null|string|IAttributes $attr optional attributes for this element
     */
    function input($value, $type='button', $attr=NULL) {
        $attr = Attr::parse($attr);
        $attr->add('type', $type);
        $attr->add('value', $value);
        $this->render('input', $attr);
    }

    /**
     * @param $value
     * @param null|string|IAttributes $attr optional attributes for this element
     */
    function button($value, $attr=NULL) {
        $this->input($value, 'button', $attr);
    }

    /**
     * @param $value
     * @param null|string|IAttributes $attr optional attributes for this element
     */
    function submit($value, $attr=NULL) {
        $this->input($value, 'submit', $attr);
    }

}