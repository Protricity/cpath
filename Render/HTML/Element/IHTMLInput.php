<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 3:16 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\IRenderHTML;

interface IHTMLInput
{
	public function getValue();
	public function setValue($value);

	public function getName();
	public function setName($value);

	public function getID();
	public function setID($value);
}

