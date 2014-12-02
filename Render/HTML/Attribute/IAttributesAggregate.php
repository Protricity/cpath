<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/1/14
 * Time: 9:02 PM
 */
namespace CPath\Render\HTML\Attribute;

interface IAttributesAggregate
{
	/**
	 * Get an IAttributes instance
	 * @return IAttributes
	 */
	function getAttributes();
}