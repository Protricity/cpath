<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/14/2014
 * Time: 10:20 PM
 */
namespace CPath\Response\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Response\IResponse;

class MapResponse implements IResponse, IKeyMap
{
	private $mMap = array();

	function __construct($message = null, $status = null) {
		foreach(func_get_args() as $arg) {
			if(is_string($arg)) {
				$this->add(IResponse::STR_MESSAGE, $arg);

			} else if (is_bool($arg) || is_int($arg)) {
				$this->add(IResponse::STR_CODE, $arg);

			} else if (is_array($arg)) {
				foreach($arg as $k=>$v) {
					$this->add($k, $v);
				}

			} else if($arg instanceof IKeyMap) {
				$this->addMap($arg);
			}
		}
	}

	public function addMap(IKeyMap $Map) {
		$this->mMap[] = $Map;
	}

	public function add($key, $value) {
		$this->mMap[$key] = $value;
	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		if($this->getMessage())
			$Map->map(IResponse::STR_MESSAGE, $this->getMessage());
		if($this->getCode())
			$Map->map(IResponse::STR_CODE, $this->getCode());
		foreach($this->mMap as $key => $value) {
			if($value instanceof IKeyMap) {
				$value->mapKeys($Map);
			} else {
				$Map->map($key, $value);
			}
		}
	}

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return isset($this->mMap[IResponse::STR_CODE]) ? $this->mMap[IResponse::STR_CODE] : IResponse::HTTP_SUCCESS;
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return isset($this->mMap[IResponse::STR_MESSAGE]) ? $this->mMap[IResponse::STR_MESSAGE] : 'Map OK';
	}
}