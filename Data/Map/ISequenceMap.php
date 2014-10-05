<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 12:58 PM
 */
namespace CPath\Data\Map;

use CPath\Request\IRequest;

interface ISequenceMap
{
	/**
	 * Map sequential data to the map
	 * @param IRequest $Request
	 * @param ISequenceMapper $Map
	 * @return void
	 */
    function mapSequence(IRequest $Request, ISequenceMapper $Map);
}

