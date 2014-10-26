<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:11 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\HTMLAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\HeaderConfig;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Request\IRequest;

class HTMLForm extends HTMLElement
{
	const TRIM_CONTENT = false;
    const CSS_FORM_FIELD = 'form-row';
    private $mAttr;

	/**
	 * @param null $method
	 * @param null $action
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
	 * @param null $_content
	 */
	public function __construct($method = null, $action = null, $classList = null, $_content = null) {
        $this->mAttr = new HTMLAttributes($classList);
        parent::__construct('form', $this->mAttr);
		if($method)
			$this->setMethod($method);
		if($action)
			$this->setAction($action);
		if($_content !== null)
			$this->addAll(array_slice(func_get_args(), 3));
    }

	public function getName()           { return $this->getAttribute('name'); }
	public function setName($value)     { $this->setAttribute('name', $value); }

	public function getID()             { return $this->getAttribute('id'); }
	public function setID($value)       { $this->setAttribute('id', $value); }

	public function setMethod($method)  { $this->mAttr->setAttribute('method', $method); }
	public function setAction($action)  { $this->mAttr->setAttribute('action', $action); }


	public function setFormValues(Array $values, IHTMLContainer $Container=null) {
		if(!$Container)
			$Container = $this;

		foreach($Container->getContent() as $Content) {
			if($Content instanceof IHTMLContainer)
				$values = $this->setFormValues($values, $Content);
			elseif(!$Content instanceof IHTMLInput)
				continue;

			$name = $Content->getName();
			if(isset($values[$name])) {
				$Content->setValue($values[$name]);
				unset($values[$name]);
			}
		}

		return $values;
//		if($values)
//			throw new \InvalidArgumentException("Form fields not found: " . implode(', ', array_keys($values)));
	}

    public function addSubmit($value = null, $name = null) {
        $this->addInput($value, $name, 'submit');
    }

    public function addInput($value = null, $name = null, $type = null) {
        $Field = new HTMLInputField($value, $type);
        if($name)
            $Field->setName($name);
        $this->addContent($Field);
    }

    /**
     * Render element content
     * @param IRequest $Request
     * @param IAttributes $ContentAttr
     */
    protected function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
	    RI::ai(1);
        echo RI::ni(), "<fieldset>";

        parent::renderContent($Request, $ContentAttr);

        echo "</fieldset>";
	    RI::ai(-1);
	    RI::ni();
    }
}