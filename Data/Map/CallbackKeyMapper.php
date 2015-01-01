<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/31/2014
 * Time: 3:51 PM
 */
namespace CPath\Data\Map;

class CallbackKeyMapper implements IKeyMapper
{
	private $mCallback;

	function __construct(\Closure $callback) {
		$this->mCallback = $callback;
	}


	/**
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool true to stop or any other value to continue
	 */
	function map($key, $value) {
		$call = $this->mCallback;

		return $call($key, $value);
	}
}