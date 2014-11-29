<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 11:11 PM
 */
namespace CPath\Request;

use CPath\Request\Log\ILogListener;

abstract class AbstractRequestWrapper implements IRequest
{
    private $mRequest;
    /** @var ILogListener[] */
    private $mLogs=array();

    function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    function getWrappedRequest() {
        return $this->mRequest;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeType() {
        return $this->mRequest->getMimeType();
    }

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return $this->mRequest->getMethodName();
    }

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mRequest->getPath();
    }

    /**
     * Matches a route prefix to this request and updates the method args with any extra path
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
        return $this->mRequest->match($routePrefix);
    }


    /**
     * Add a log entry
     * @param mixed $msg The log message
     * @param int $flags [optional] log flags
     * @return int the number of listeners that processed the log entry
     */
    function log($msg, $flags = 0) {
	    $c = 0;
        foreach($this->mLogs as $Log)
            $c += $Log->log($msg, $flags);

        $c += $this->mRequest->log($msg, $flags);
	    return $c;
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
        $this->mLogs[] = $Listener;
    }

    /**
     * @param bool $withDomain
     * @return String
     */
    function getDomainPath($withDomain = false) {
        return $this->mRequest->getDomainPath($withDomain);
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
		return $this->mRequest->offsetExists($offset);
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
		return $this->mRequest->offsetGet($offset);
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
		$this->mRequest->offsetSet($offset, $value);
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
		$this->mRequest->offsetUnset($offset);
	}
}