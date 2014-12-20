<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 2:27 PM
 */
namespace CPath\Data\Map;

class CallbackKeyMap implements IKeyMap
{
	private $mCallback;

	function __construct(\Closure $callback) {
		$this->mCallback = $callback;
	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		$call = $this->mCallback;
		$call($Map);
	}
}