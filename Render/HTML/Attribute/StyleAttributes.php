<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 9:49 PM
 */
namespace CPath\Render\HTML\Attribute;

class StyleAttributes extends Attributes
{
	public function __construct($name, $value = null) {
		if (is_array($name)) {
			foreach ($name as $key => $value)
				$this->setStyle($key, $value);
		} else {
			$this->setStyle($name, $value);
		}
	}
}