<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/9/14
 * Time: 9:17 PM
 */
namespace CPath\Data\Map;

use CPath\Request\IRequest;

interface IKeyMapInstance
{
	/**
	 * Get a new object instance based on mapped keys
	 * @param IRequest $Request
	 * @param IKeyMap $Map the map to grab values from
	 * @return IKeyMapInstance
	 */
	static function getMappedInstance(IRequest $Request, IKeyMap $Map);
}