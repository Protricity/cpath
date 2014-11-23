<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:36 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Common\HTMLText;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Common\IInputField;
use CPath\Request\Parameter\IRequestParameter;

class HTMLLabel extends HTMLElement
{
	const PASS_DOWN_ATTRIBUTES = true;

	/**
	 * @param string $text
	 * @param String|\CPath\Render\HTML\Attribute\IAttributes $classList
	 * @param null $_content
	 */
    public function __construct($text=null, $classList=null, $_content=null) {
        parent::__construct('label', $classList);
	    if($text)
	        $this->addContent(new HTMLText($text));

	    if($_content !== null)
		    for($i=2;$i<func_num_args();$i++)
			    $this->addAll(func_get_arg($i));
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML|string $Render
     * @param null $key
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Render, $key = null) {
	    $name = null;
	    $description = null;
	    if ($Render instanceof IRequestParameter) {
		    $description = $Render->getDescription();
		    $name = $Render->getFieldName();

	    } elseif ($Render instanceof IInputField) {
		    $name = $Render->getFieldName();

	    }

	    if($name)
		    $this->setAttribute('for', $name);

	    parent::addContent(new HTMLText($description));
        parent::addContent($Render, $key);
    }
}


