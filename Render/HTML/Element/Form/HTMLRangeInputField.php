<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/2/2015
 * Time: 5:11 PM
 */
namespace CPath\Render\HTML\Element\Form;

class HTMLRangeInputField extends HTMLInputField
{
	public function __construct($name = null, $value = null, $min = null, $max = null, $step = null, $classList = null, $_content = null) {
		parent::__construct($name, $value, 'range');
		is_string($min)   ? $this->setMin($min)   : $this->addVarArg($min);
		is_string($max)   ? $this->setMax($max)   : $this->addVarArg($max);
		is_string($step)   ? $this->setStep($step)   : $this->addVarArg($step);
		is_string($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=6; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

	public function setMin($min) { $this->setAttribute('min', $min); }

	public function setMax($min) { $this->setAttribute('max', $min); }

	public function setStep($min) { $this->setAttribute('step', $min); }
}