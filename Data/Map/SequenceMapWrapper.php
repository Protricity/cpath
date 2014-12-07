<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/9/14
 * Time: 9:50 AM
 */
namespace CPath\Data\Map;

class SequenceMapWrapper implements ISequenceMap
{

	private $mMap;

	public function __construct(ISequenceMap $Map) {
		$this->mMap = $Map;
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @internal param \CPath\Data\Map\IRequest $Request
	 * @internal param \CPath\Data\Map\IRequest $Request
	 * @return mixed
	 */
	function mapSequence(ISequenceMapper $Map) {
		$this->mMap->mapSequence($Map);
	}
}