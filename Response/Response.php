<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Request\IRequest;
use Traversable;

class Response implements IResponse, IResponseHeaders, IKeyMap,  \ArrayAccess, \IteratorAggregate {
    private $mCode, $mMessage;
	private $mHeaders = array();
    private $mData = array();
    /** @var IKeyMap[] */
    private $mMaps = array();

    /**
     * Create a new response
     * @param String $message the response message
     * @param int|bool $status the response status code or true/false for success/error
     * @internal param mixed $data additional response data
     */
    function __construct($message=NULL, $status=true) {
        $this->setStatusCode($status);
        $this->setMessage($message);
    }

    function setData($key, $value) {
        $this->mData[$key] = $value;
        return $this;
    }

    function getData($key) {
        return $this->mData[$key];
    }

	/**
	 * Add response headers to this response object
	 * @param String $name i.e. 'Location' or 'Location: /path'
	 * @param String|null $value i.e. '/path'
	 * @return $this
	 */
	function addHeader($name, $value=null) {
		$this->mHeaders[$name] = $value;
		return $this;
	}

	/**
	 * Send response headers for this response
	 * @param IRequest $Request
	 * @param string $mimeType
	 * @return bool returns true if the headers were sent, false otherwise
	 */
	function sendHeaders(IRequest $Request, $mimeType = null) {
		if(headers_sent())
			return false;

		$msg = $this->getMessage();
		$msg =  preg_replace('/[^\w -]/', '', $msg);
		if(strlen($msg) > 64)
			$msg = substr($msg, 0, 64) . '...';

		$code = is_numeric($this->getCode()) ? (int)$this->getCode() : 400;
//		\http_response_code($code);

		header("HTTP/1.1 " . $this->getCode() . " " . $msg);
		header("Content-Type: " . $mimeType);

		foreach($this->mHeaders as $name => $value)
			switch($name) {
				default:
					header($value === null ? $name : $name . ': ' . $value);
					break;
			}

		return true;
	}

    function getCode() {
        return $this->mCode;
    }

    /**
     * @param int|bool $status
     * @return $this
     */
    function setStatusCode($status) {
        if(is_int($status))
            $this->mCode = $status;
        else if(!is_null($status))
            $this->mCode = $status ? IResponse::HTTP_SUCCESS : IResponse::HTTP_ERROR;
        return $this;
    }

    /**
     * Get the Response Message
     * @return String
     */
    function getMessage() {
        return $this->mMessage;
    }

    /**
     * Set the message and return the Response
     * @param $msg
     * @return $this
     */
    function setMessage($msg) {
        $this->mMessage = $msg;
        return $this;
    }

    /**
     * Update and return the Response
     * @param $msg
     * @param $status
     * @return $this
     */
    function update($msg=null, $status=null) {
        if($msg !== null)
            $this->setMessage($msg);
        if($status !== null)
            $this->setStatusCode($status);
        return $this;
    }

    /**
     * Map data to the key map
     * @param IKeyMapper $Mapper
     * @return void
     */
    function mapKeys(IKeyMapper $Mapper) {
        $Mapper->map(IResponse::STR_MESSAGE, $this->getMessage());
        $Mapper->map(IResponse::STR_CODE, $this->getCode());
        foreach($this->mData as $key => $value)
            $Mapper->map($key, $value);
        foreach($this->mMaps as $Map)
            $Map->mapKeys($Mapper);
    }

    /**
     * Add a key map to the response key map
     * @param IKeyMap $Map
     */
    public function addMap(IKeyMap $Map) {
        $this->mMaps[] = $Map;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->mData);
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
        return isset($this->mData[$offset]);
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
        return $this->mData[$offset];
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
        $this->mData[$offset] = $value;
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
        unset($this->mData[$offset]);
    }
}
