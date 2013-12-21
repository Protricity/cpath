<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Util;

use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

class HTMLRenderUtil {
    private $mRequest;

    public function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    function render($nodeType, $class=null, $attr=null) {
        echo RI::ni(), "<", $this->renderElementContent($nodeType, $attr, $class), "/>";
    }

    function open($nodeType, $class=null, $attr=null) {
        echo RI::ni(), "<", $this->renderElementContent($nodeType, $attr, $class), ">";
        RI::ai(1);
    }

    function close($nodeType) {
        RI::ai(-1);
        echo RI::ni(), '</', $nodeType, ">";
    }

    function formOpen($class=null, $attr=null) {
        $this->open('form', $class, $attr);
    }

    function formClose() {
        $this->close('form');
    }

    function input($value, $type='button', $class=null, $attr=null) {
        $this->render('input', $class, $this->getAttr($attr, array('type' => $type, 'value' => $value)));
    }

    function button($value, $class=null, $attr=null) {
        $this->input($value, 'button', $class, $attr);
    }

    function submit($value, $class=null, $attr=null) {
        $this->input($value, 'submit', $class, $attr);
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
     * @param $attr
     * @param $class
     * @return String
     */
    private function renderElementContent($nodeType, $attr, $class) {
        echo $nodeType;
        $class = $this->getClassString($class);
        if($attr = $this->getAttr($attr, array('class' => $class)))
            echo ' ', $attr;
    }
}