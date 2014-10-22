<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/9/14
 * Time: 10:06 PM
 */
namespace CPath\Data\Map;

use CPath\Request\IRequest;

class KeyMapArray implements IKeyMap
{
	private $mValues;

	public function __construct(Array $values) {
		$this->mValues = $values;
	}

	function getValues() {
		return $this->mValues;
	}

//	public function add($key, $value) {
//		$this->mValues[$key] = $value;
//	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		foreach ($this->mValues as $key => $value)
			if ($Map->map($key, $value) === true)
				break;
	}
}