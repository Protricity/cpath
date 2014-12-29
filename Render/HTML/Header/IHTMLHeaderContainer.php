<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/28/2014
 * Time: 2:36 PM
 */
namespace CPath\Render\HTML\Header;

interface IHTMLHeaderContainer
{
	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @return void
	 */
	function addSupportHeaders(IHTMLSupportHeaders $Headers);

	/**
	 * Get meta tag content or return null
	 * @param String $name tag name
	 * @return String|null
	 */
	// function getMetaTagContent($name);

	/**
	 * @return IHTMLSupportHeaders[]
	 */
	// function getSupportHeaders();
}