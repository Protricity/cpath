<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/19/2014
 * Time: 8:45 PM
 */
namespace CPath\Build\Code;

interface IConstructorArgs
{
	/**
	 * Return a list of args that could be called to initialize this class object
	 * @return array
	 */
	function getConstructorArgs();
}