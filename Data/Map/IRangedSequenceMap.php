<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 12:10 PM
 */
namespace CPath\Data\Map;

use CPath\Request\IRequest;

interface IRangedSequenceMap extends ISequenceMap
{
	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @return
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param int $start
	 * @internal param int|null $limit
	 */
	function mapSequence(ISequenceMapper $Map);
}