<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 11:49 AM
 */
namespace CPath\Data\Value;

use CPath\Request\IRequest;

interface IHasURL
{
	function getURL(IRequest $Request = null);
}