<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/15/14
 * Time: 5:18 PM
 */
namespace CPath\Render\HTML\Attribute;

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
	 * Returns an array of classes
	 * @return Array
	 */
	function getClasses() {
		$classes = array();
		foreach ($this->mAttributes as $Attributes)
			$classes = array_merge($classes, $Attributes->getClasses());

		return array_unique($classes);
	}

	/**
	 * Return the style value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getStyle($name = null) {
		$styles = array();
		foreach ($this->mAttributes as $Attributes)
			$styles += $Attributes->getStyle();
		if ($name)
			return isset($styles[$name]) ? $styles[$name] : null;

		return $styles;
	}

	/**
	 * Return the attribute value or a name-value associative array
	 * @param null $name
	 * @return String|Array
	 */
	function getAttribute($name = null) {
		$attributes = array();
		foreach ($this->mAttributes as $Attributes)
			$attributes += $Attributes->getAttribute();
		if ($name)
			return isset($attributes[$name]) ? $attributes[$name] : null;

		return $attributes;
	}


	/**
	 * Render html attributes
	 * @param IAttributes|null $Additional
	 * @param IAttributes $_Additional
	 * @return string|void always returns void
	 */
	function render(IAttributes $Additional = null, IAttributes $_Additional = null) {
		$classes    = $this->getClasses();
		$styles     = $this->getStyle();
		$attributes = $this->getAttribute();

		foreach (func_get_args() as $Additional) {
			if ($Additional instanceof IAttributes) {
				$classes = array_merge($classes, $Additional->getClasses());
				$styles += $Additional->getStyle();
				$attributes += $Additional->getAttribute();
			}
		}

		if ($classes) {
			$i = 0;
			echo ' class=\'';
			foreach ($classes as $class)
				echo($i++ ? ' ' : ''), $class;
			echo '\'';
		}

		if ($styles) {
			$i = 0;
			echo ' style=\'';
			foreach ($styles as $name => $value)
				echo($i++ ? '; ' : ''), $name . ": " . $value;
			echo '\'';
		}

		foreach ($attributes as $key => $value)
			echo ' ' . $key . "='" . $value . "'";
	}

	function __toString() {
		ob_start();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
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