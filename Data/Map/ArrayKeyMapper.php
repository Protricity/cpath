<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/3/2015
 * Time: 3:47 PM
 */
namespace CPath\Data\Map;

class ArrayKeyMapper implements IKeyMapper
{
	private $mArray = array();

	function getArray() {
		return $this->mArray;
	}

	/**
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool true to stop or any other value to continue
	 */
	function map($key, $value) {
        if(is_array($value)) {
            $this->mArray[$key] = $value;

        } elseif ($value instanceof IKeyMap) {
            $this->mArray[$key] = self::mapToArray($value);

        } else {
            $this->mArray[$key] = $value;


        }

		return true;
	}

	// Static

	static function mapToArray(IKeyMap $Map) {
		$Inst = new ArrayKeyMapper();
		$Map->mapKeys($Inst);

		return $Inst->getArray();
	}
}