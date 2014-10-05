<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:30 PM
 */
namespace CPath\Data\Map;

class MappableKeysCallback implements IKeyMap
{
    private $mCallback;

    function __construct(\Closure $callback) {
        $this->mCallback = $callback;
    }

	/**
	 * Map data to a data map
	 * @param IRequest $Request
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
    function mapKeys(IRequest $Request, IKeyMapper $Map) {
        $call = $this->mCallback;
        $call($Map);
    }
}