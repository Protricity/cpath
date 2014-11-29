<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 2:06 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;

class HTMLFileInputField extends HTMLFormField
{

	/**
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $name
	 * @param null $accept
	 */
	public function __construct($classList = null, $name = null, $accept = null) {
		parent::__construct($classList, $name, null, 'file');
		if($accept)
			$this->setAccept($accept);
	}

	public function setAccept($accept) {
		$this->setAttribute('accept', $accept);
	}

	// Static

//	static function get($description = null, $classList = null, $name = null) {
//		return new HTMLFileInputField($description, $classList, $name);
//	}
}