<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:11 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\HTMLAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Framework\Render\Util\RenderIndents as RI;

class HTMLForm extends HTMLElement
{
    const CSS_FORM_FIELD = 'form-row';
    private $mAttr;

    public function __construct($method = 'POST', $action = null, $name = null, $attr = null) {
        $this->mAttr = new HTMLAttributes($attr);
        if ($method !== null)
            $this->mAttr->setAttribute('method', $method);
        if ($name !== null)
            $this->mAttr->setAttribute('name', $name);
        if ($action !== null)
            $this->mAttr->setAttribute('action', $action);
        parent::__construct('form', $this->mAttr);
    }

    public function addSubmit($value = null, $name = null) {
        $this->addInput($value, $name, 'submit');
        return $this;
    }

    public function addInput($value = null, $name = null, $type = null) {
        $Field = new HTMLInputField($value, $type);
        if($name)
            $Field->setAttribute('name', $name);
        $this->addContent($Field);
        return $this;
    }

    /**
     * Render element content
     * @param IRequest $Request
     * @param IAttributes $ContentAttr
     */
    protected function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
        echo RI::ni(), "<fieldset>";
        RI::ai(1);

        parent::renderContent($Request, $ContentAttr);

        RI::ai(-1);
        echo RI::ni(), "</fieldset>";
    }
}