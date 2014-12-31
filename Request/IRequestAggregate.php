<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/29/2014
 * Time: 6:02 PM
 */
namespace CPath\Request;

interface IRequestAggregate
{
	/**
	 * Return the aggregate request instance
	 * @return IRequest
	 */
	function getWrappedRequest();
}