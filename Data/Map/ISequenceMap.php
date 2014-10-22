<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 12:58 PM
 */
namespace CPath\Data\Map;

interface ISequenceMap
{
	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 */
	function mapSequence(ISequenceMapper $Map);
}

