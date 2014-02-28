<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Util;

use CPath\Framework\Render\Interfaces\IAttributes;
use CPath\Framework\Render\Util\Attr;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;

class HTMLRenderUtil {
    private $mRequest;

    public function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    /**
     * @param $nodeType
     * @param String|IAttributes|Null $Attr optional attributes for this element
     */
    function render($nodeType, $Attr=null) {
        echo RI::ni(), "<", $this->renderElementContent($nodeType, $Attr), "/>";
    }

    /**
     * @param $nodeType
     * @param String|IAttributes|Null $Attr optional attributes for this element
     */
    function open($nodeType, $Attr=null) {
        echo RI::ni(), "<", $this->renderElementContent($nodeType, $Attr), ">";
        RI::ai(1);
    }

    function close($nodeType) {
        RI::ai(-1);
        echo RI::ni(), '</', $nodeType, ">";
    }

    /**
     * @param String|IAttributes|Null $Attr optional attributes for this element
     */
    function formOpen($Attr=NULL) {
        $this->open('form', $Attr);
    }

    function formClose() {
        $this->close('form');
    }

    /**
     * @param $value
     * @param $type
     * @param String|IAttributes|Null $Attr optional attributes for this element
     */
    function input($value, $type='button', $Attr=NULL) {
        $Attr = Attr::get($Attr);
        $Attr->add('type', $type);
        $Attr->add('value', $value);
        $this->render('input', $Attr);
    }

    /**
     * @param $value
     * @param String|IAttributes|Null $Attr optional attributes for this element
     */
    function button($value, $Attr=NULL) {
        $this->input($value, 'button', $Attr);
    }

    /**
     * @param $value
     * @param String|IAttributes|Null $Attr optional attributes for this element
     */
    function submit($value, $Attr=NULL) {
        $this->input($value, 'submit', $Attr);
    }



    /**
     * @param $class
     * @param null $additionalClass
     * @return String
     */
    function getClass($class, $additionalClass=null) {
        $class = $this->getClassString($class);
        if($additionalClass)
            $class = ($class ? $class . ' ' : '') . $this->getClassString($additionalClass);
        return $class;
    }

    /**
     * @param $attr
     * @param null $additionalAttr
     * @return String
     */
    function getAttr($attr, $additionalAttr=null) {
        $attr = $this->getAttrString($attr);
        if($additionalAttr)
            $attr = ($attr ? $attr . ' ' : '') . $this->getAttrString($additionalAttr);
        return $attr;
    }


    private function getAttrString($attr) {
        if(is_array($attr)) {
            if(key($attr) !== 0) {
                $attr2 = '';
                foreach($attr as $k=>$v)
                    $attr2 .= ($attr2 ? ' ' : '') . $k . "='" . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . "'";
                $attr = $attr2;
            } else {
                $attr = implode(' ', $attr);
            }
        }

        return $attr;
    }

    private function getClassString($class) {
        if(is_array($class))
            $class = implode(' ', $class);
        return $class;
    }

    /**
     * @param $nodeType
     * @param String|IAttributes|Null $Attr optional attributes for this element
     * @return null|String always returns null
     */
    private function renderElementContent($nodeType, $Attr=null) {
        echo $nodeType;
        if($Attr)
            Attr::get($Attr)->render($this->mRequest);
    }
}