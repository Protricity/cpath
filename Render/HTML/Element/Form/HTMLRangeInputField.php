<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/2/2015
 * Time: 5:11 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Element\HTMLInputField;

class HTMLRangeInputField extends HTMLInputField
{
	public function __construct($name = null, $value = null, $min = null, $max = null, $step = null, $_content = null) {
		parent::__construct($name, $value, 'range');
		if (is_scalar($min)) $this->setMin($min);
		if (is_scalar($max)) $this->setMax($max);
		if (is_scalar($step)) $this->setStep($step);

		foreach (func_get_args() as $i => $arg)
			if (!is_string($arg))
				$this->addVarArg($arg);
	}

	public function setMin($min) { $this->setAttribute('min', $min); }

	public function setMax($min) { $this->setAttribute('max', $min); }

	public function setStep($min) { $this->setAttribute('step', $min); }
}