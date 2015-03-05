<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 2:16 PM
 */
namespace CPath\Request;

use CPath\Render\HTML\Element\Form\HTMLForm;
use CPath\Render\HTML\Element\Form\HTMLInputField;
use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\Web\CLIWebRequest;
use CPath\Request\Web\WebFormRequest;
use CPath\Request\Web\WebRequest;
use Traversable;

class Request implements IRequest
{
	private $mParameters;
    /** @var ILogListener[] */
    private $mLogListeners=array();

    private $mMethodName;
    /** @var IRequestedMimeType */
    private $mMimeType=null;

	private $mRequestedParams = array();

    public function __construct($method, $path, $parameters = array(), IRequestedMimeType $MimeType=null) {
        $this->mMethodName = $method;
        $this->mPath = $path ? '/' . ltrim($path, '/') : '/';
        $this->mParameters = $parameters;
        $this->mMimeType = $MimeType;
    }

    /**
     * Get the Request Method (POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return $this->mMethodName;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeType() {
        return $this->mMimeType;
    }

	/**
	 * Return the route path for this request
	 * @return String the route path starting with '/'
	 */
	function getPath() {
		return $this->mPath;
	}

	protected function getNamedRequestValue($fullParameterName, $array, $prefix=null, $filter=FILTER_SANITIZE_SPECIAL_CHARS) {
		if(isset($array[$fullParameterName])) {
            $value = $array[$fullParameterName];
            if(!$filter)
                return $value;
            return is_scalar($value) ? filter_var($value, $filter) : $value;
        }

		foreach($array as $key => $item) {
			if($prefix)
				$key = $prefix . '[' . $key . ']';

			if(strpos($fullParameterName, $key) !== 0)
				continue;

            if(is_null($item))
                return $item;

            if(is_scalar($item))
                return filter_var($item, $filter);

            if($fullParameterName === $key)
                return $item;

			$value = $this->getNamedRequestValue($fullParameterName, $item, $key);
			if($value !== null)
				return $value;
		}

		return null;
	}

    /**
     * @param $paramName
     * @param int $filter
     * @return String|null
     */
	function getRequestValue($paramName, $filter=FILTER_SANITIZE_SPECIAL_CHARS) {
		return $this->getNamedRequestValue($paramName, $this->mRequestedParams, null, $filter)
			?: $this->getParameterValue($paramName, $filter);
	}

	function getParameterValue($paramName, $filter=FILTER_SANITIZE_SPECIAL_CHARS) {
		return $this->getNamedRequestValue($paramName, $this->mParameters, null, $filter);
	}

	function getParameterValues() {
		return $this->mParameters;
	}

	/**
	 * Matches a route prefix to this request and updates the method args with any extra path
	 * @param $routePrefix '[method] [path]'
	 * @param int $flags
	 * @return bool true if the route matched
	 */
    function match($routePrefix, $flags=0) {

//	    if($flags) {
//		    if($flags & IRequest::MATCH_NO_SESSION) {
//			    if($this instanceof ISessionRequest
//				    && $this->hasSessionCookie())
//				    return false;
//		    }
//		    elseif($flags & IRequest::MATCH_SESSION_ONLY) {
//			    if(!$this instanceof ISessionRequest
//				    || !$this->hasSessionCookie())
//				    return false;
//		    }
//	    }

	    if(strpos($routePrefix, ' ') !== false)
            list($routeMethod, $path) = explode(' ', $routePrefix, 2);
	    else
		    list($routeMethod, $path) = array('ANY', $routePrefix);

        $requestMethod = $this->getMethodName();

//        if($this instanceof ILogListener)
//            $this->log("Matching " . $this->getPath() . " to " . $routePrefix);
        // /user/abc123/
        // /user/:id/
        if ($routeMethod !== 'ANY' && $routeMethod !== $requestMethod)
            return false;

        if(($p = strpos($path, ':')) !== false) {
            $routeArgs = explode('/', trim($path, '/'));
            $i=0;
            foreach(explode('/', trim($this->getPath(), '/')) as $requestPathArg) {
                if(!isset($routeArgs[$i]))
                    return false;

                $routeArg = $routeArgs[$i++];

                if($routeArg[0] == ':') {
                    $this->mParameters[substr($routeArg, 1)] = $requestPathArg;

                } elseif(strcasecmp($routeArg, $requestPathArg) !== 0) {
                    return false;

                }
            }

	        while(isset($routeArgs[$i])) {
		        $routeArg = $routeArgs[$i];
		        if($routeArg[0] === ':') {
			        // There was more variable path, so the variable goes null
			        $this->mParameters[substr($routeArg, 1)] = null;
			        $i++;
			        continue;
		        }

		        return false; // There was more path and it wasn't variable
	        }

        } else {
            if(strpos($path, '*') !== false) {
                $path = preg_quote($path, '/');
                $pattern = str_replace( '\*' , '.*?', $path);
                if(!preg_match( '/^' . $pattern . '$/i', $this->getPath()))
                    return false;
            } elseif (strcasecmp(rtrim($this->getPath(), '/'), rtrim($path, '/')) !== 0) {
                return false;
            }

        }

//        $this->log("Matched " . $routePrefix);
        return true;
    }


    /**
     * Add a log entry
     * @param mixed $msg The log message
     * @param int $flags [optional] log flags
     * @return int the number of listeners that processed the log entry
     */
    function log($msg, $flags = 0) {
	    $c = 0;
        foreach($this->mLogListeners as $Log)
            $c += $Log->log($msg, $flags);

        $MimeType = $this->getMimeType();
        if($MimeType instanceof ILogListener)
	        $c += $MimeType->log($msg, $flags);

	    return $c;
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
	    if(!in_array($Listener, $this->mLogListeners))
            $this->mLogListeners[] = $Listener;
    }
//
//    /**
//     * Returns an associative array of params and their descriptions
//     * @return array
//     */
//    function getParameterDescriptions() {
//        return $this->mParams;
//    }

    /**
     * @param bool $withDomain
     * @return String
     */
    function getDomainPath($withDomain=false) {
        return '';
    }

    // Static

	/**
	 * Create a new IRequest inst from environment variables
	 * @param String $route path string or route ([method] [path])
	 * @param array $args
	 * @param IRequestedMimeType $MimeType
	 * @return IRequest
	 */
    public static function create($route=null, Array $args=array(), IRequestedMimeType $MimeType=null) {
        $method = null;
        if(($p = strpos($route, ' ')) !== false)
            if($p <=5)
                list($method, $route) = explode(' ', $route, 2);
        //static $Inst = null;
        //if($Inst) return $Inst;

        if (PHP_SAPI === 'cli') {
            $Inst = new CLI\CLIRequest($route, $args);
        } else {
            if(!$method)
                $method = $_SERVER["REQUEST_METHOD"];
            if ($method === 'GET')
                $Inst = new WebRequest($method, $route, $args, $MimeType);
            elseif ($method === 'CLI')
                $Inst = new CLIWebRequest($route, $args);
            else
                $Inst = new WebFormRequest($method, $route, $args);
        }
        return $Inst;
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
		return $this->getRequestValue($offset) !== null;
	}

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @param int $filter
     * @throws Validation\Exceptions\ValidationException
     * @return mixed Can return all value types.
     */
	public function offsetGet($offset, $filter=FILTER_SANITIZE_SPECIAL_CHARS) {
		if($offset[strlen($offset)-1] === '?') {
			$value = $this->getRequestValue(substr($offset, 0, -1), $filter);
			return $value;
		}

		$value = $this->getRequestValue($offset, $filter);

		if($value === null) {
			static $onlyOnce = false;
			if(!$onlyOnce) {
				$onlyOnce = true;
				$Form = new HTMLForm($this->getMethodName(), $this->getPath());
				foreach ($this->mRequestedParams as $param)
					$Form->addAll(
						$param,
						new HTMLInputField($param
//							new RequiredValidation()
						)
					);

				$Form->validateRequest($this);
			}
		}
		return $value;
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
		$this->mParameters[$offset] = $value;
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
		unset($this->mParameters[$offset]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIterator() {
		return new \ArrayIterator($this->mParameters);
	}
}