<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 5:18 PM
 */
namespace CPath\Render\HTML\Attribute;

use CPath\Request\IRequest;

class AttributeCollection implements IAttributes
{
	/** @var IAttributes[] */
	private $mAttributes = array();

	public function __construct(IAttributes $Attributes = null, IAttributes $_Attributes = null) {
		$this->mAttributes = func_get_args();
	}

	function addAttributes(IAttributes $Attributes, IAttributes $_Attributes=null) {
		foreach(func_get_args() as $Attributes) {
			$this->mAttributes[] = $Attributes;
		}
	}

	/**
	 * Return the attribute value
	 * @param String $name
	 * @return String|null
	 */
	function getAttribute($name) {
		foreach($this->mAttributes as $Attribute)
			if($value = $Attribute->getAttribute($name))
				return $value;
		return null;
	}

	/**
	 * Render or returns html attributes
	 * @param IRequest $Request
	 * @internal param bool $return if true, the attributes are returned as a string rather than echoed
	 * @return string|void
	 */
	function renderHTMLAttributes(IRequest $Request=null) {
		foreach($this->mAttributes as $Attributes)
			$Attributes->renderHTMLAttributes($Request);
	}

	/**
	 * Return an associative array of attribute name-value pairs
	 * @param \CPath\Request\IRequest $Request
	 * @return string
	 */
	function getHTMLAttributeString(IRequest $Request = null) {
		$content = '';
		foreach($this->mAttributes as $Attributes)
			$content .= $Attributes->getHTMLAttributeString();
		return $content;
	}

	function __toString() {
		return $this->getHTMLAttributeString();
	}


	// Static

	/**
	 * @param String|IAttributes $attributes class list or IAttributes inst
	 * @param String|IAttributes $_attributes [varargs] class list or IAttributes inst
	 * @return \CPath\Render\HTML\Attribute\AttributeCollection
	 */
	static function combine($attributes, $_attributes) {
		$Inst = new AttributeCollection();
		foreach (func_get_args() as $attributes) {
			if (is_null($attributes));
			elseif (is_string($attributes))
				$Inst->addAttributes(new ClassAttributes($attributes));
			elseif(is_array($attributes))
				self::combineA($attributes, $Inst);
			else
				$Inst->addAttributes($attributes);
		}

		return $Inst;
	}

	/**
	 * @param array|\CPath\Render\HTML\Attribute\IAttributes|String $attributes class list or IAttributes inst
	 * @param AttributeCollection $Inst
	 * @return \CPath\Render\HTML\Attribute\AttributeCollection
	 */
	static function combineA(Array $attributes, AttributeCollection $Inst=null) {
		$Inst = $Inst ?: new AttributeCollection();
		foreach ($attributes as $attribute) {
			if(!$attributes)
				continue;
			if (is_string($attribute))
				$attribute = new ClassAttributes($attribute);
			$Inst->addAttributes($attribute);
		}

		return $Inst;
	}
}