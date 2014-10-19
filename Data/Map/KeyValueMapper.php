<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/9/14
 * Time: 9:28 PM
 */
namespace CPath\Data\Map;

use CPath\Request\IRequest;

class KeyValueMapper implements \ArrayAccess
{
	private $mMap;
	private $mRequest;
	private $mValues = array();

	/**
	 * @param IRequest $Request
	 * @param IKeyMap $Map
	 */
	function __construct(IRequest $Request, IKeyMap $Map) {
		$this->mMap = $Map;
		$this->mRequest = $Request;
	}

	function getValue($key) {
		$values = $this->getMappedValues();
		return $values[$key];
	}

	function getMappedValues($_key=null) {
		if($this->mValues === null) {
			if($this->mMap instanceof KeyMapArray) {
				$values = $this->mMap->getValues();

			} else {
				$values = array();
				$this->mMap->mapKeys($this->mRequest,
					new CallbackKeyMapper(
						function($key, $value) use (&$values) {
							$values[$key] = $value;
						}
					)
				);
			}
			$this->mValues = $values;
		}

		if($_key === null)
			return $this->mValues;

		$keyValues = array();
		foreach(func_get_args() as $key)
			if(isset($this->mValues[$key]))
				$keyValues[$key] = $this->mValues[$key];

		return $keyValues;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		$values = $this->getMappedValues();
		return isset($values[$offset]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		$values = $this->getMappedValues();
		return $values[$offset];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		throw new \InvalidArgumentException("Not implemented");
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function offsetUnset($offset) {
		throw new \InvalidArgumentException("Not implemented");
	}
}