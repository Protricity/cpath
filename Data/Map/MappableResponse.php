<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:48 AM
 */
namespace CPath\Data\Map;

use CPath\Render\HTML\Attribute;
use CPath\Response\IResponse;
use CPath\Response\Response;

class MappableResponse extends Response implements IKeyMap, \ArrayAccess
{
	/** @var IKeyMap */
    private $mMappable;
	private $mValues = array();

	/**
	 * Create a new response
	 * @param IKeyMap|array $Mappable
	 * @param String $message the response message
	 * @param null $contentKeyName
	 * @param bool $status
	 * @internal param bool|int $status the response status code or true/false for success/error
	 */
    function __construct(IKeyMap $Mappable=null, $message=null, $contentKeyName=null, $status=true) {
        parent::__construct($message, $status);
	    $this->mMappable = $Mappable;
    }

	function getMappable() {
		return $this->mMappable;
	}

	function setKeyValue($keyName, $value) {
		$this->mValues[$keyName] = $value;
	}

	/**
	 * Map data to a data map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
    function mapKeys(IKeyMapper $Map) {
	    $Map->map(IResponse::STR_MESSAGE, $this->getMessage());
	    $Map->map(IResponse::STR_CODE, $this->getCode());

	    if($this->mMappable) {
			$this->mMappable->mapKeys($Map);
	    }

	    foreach($this->mValues as $key=>$value) {
			$Map->map($key, $value);
	    }
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
		return isset($this->mValues[$offset]);
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
		return $this->mValues[$offset];
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
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->mValues[$offset] = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->mValues[$offset]);
	}
}