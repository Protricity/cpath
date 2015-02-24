<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 4:25 PM
 */
namespace CPath\UnitTest;

use CPath\Request\AbstractRequestWrapper;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Session\ISessionRequest;
use CPath\Request\Session\SessionRequestException;
use CPath\UnitTest\Exceptions\UnitTestException;

class UnitTestRequestWrapper extends AbstractRequestWrapper implements IUnitTestRequest, ISessionRequest, IFormRequest
{
    private $mFlags;
	private $mAssertionCount = 0;
	private $mTestSession = array();
	private $mTestParameters = array();
	private $mMethodMocks = array();

    function __construct(IRequest $Request, $flags = null) {
        parent::__construct($Request);
        $this->mFlags = $flags;
    }

    /**
     * Test values for one or more flags
     * @param String $_flag vararg of flags.
     * ->hasFlag(FLAG1 | FLAG2, FLAG3) returns true IF (either FLAG1 OR FLAG2 is set) AND (FLAG3 is set)
     * @return bool
     */
    function hasFlag($_flag) {
        foreach(func_get_args() as $arg)
            if(!($arg & $this->mFlags))
                return false;

        return true;
    }

    /**
     * Assert condition is true or throws an exception
     * @param bool|IUnitTestAssertion $condition
     * @param String $message
     * @return void
     * @throws UnitTestException
     */
    function assert($condition, $message = null) {
	    if($condition instanceof IUnitTestAssertion)
		    $condition->assert($message);
        elseif($condition !== true)
            throw new UnitTestException($message);
	    $this->mAssertionCount++;
    }

	/**
	 * Assert variables are equal or throws an exception
	 * @param String $expected
	 * @param String $actual
	 * @param null $message
	 * @return void
	 */
	function assertEqual($expected, $actual, $message = null) {
		$this->assert(new AssertEquals($expected, $actual), $message);
	}

	/**
	 * Assert a fail condition. Throws a UnitTestException
	 * @param $message
	 * @return mixed
	 * @throws \CPath\UnitTest\Exceptions\UnitTestException
	 */
	function fail($message) {
		$this->assert(false, $message);
	}

	function getAssertionCount() {
		return $this->mAssertionCount;
	}

	/**
	 * Return a referenced array representing the request session
	 * @param String|null [optional] $key if set, retrieves &$[Session][$key] instead of &$[Session]
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	function &getSession($key = null) {
		if($key === null)
			return $this->mTestSession;
        if(!isset($this->mTestSession[$key]))
	        $this->mTestSession[$key] = array();

        return $this->mTestSession[$key];
//
//		$Request = $this->getWrappedRequest();
//		if($Request instanceof ISessionRequest)
//			return $Request->getSession($key);
//
//		throw new \InvalidArgumentException("Wrapped activeRequests does not implement ISessionRequest");
	}

	/**
	 * Returns true if the session is active, false if inactive
	 * @return bool
	 */
	function isStarted() {
		return true;
	}

	/**
	 * Start a new session
	 * @internal param bool $reset if true, session will be reset
	 * @return bool true if session was started, otherwise false
	 */
	function startSession() {
		$this->mTestSession = array();
		return true;
	}

	/**
	 * End current session
	 * @throws \InvalidArgumentException
	 * @return bool true if session was started, otherwise false
	 */
	function endSession() {
		$this->mTestSession = array();
		return true;
	}

	/**
	 * Destroy session data
	 * @return bool true if session was destroyed, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function destroySession() {
		$this->mTestSession = array();
	}

	/**
	 * Returns true if the session is active, false if inactive
	 * @return bool
	 */
	function hasSessionCookie() {
		return true;
	}


	/**
	 * Set a test request parameter for unit test purposes
	 * @param $name
	 * @param $value
	 */
	function setRequestParameter($name, $value) {
		$this->mTestParameters[$name] = $value;
	}

    /**
     * Return a request value
     * @param $fieldName
     * @param bool|int $filter
     * @return mixed|null the form field value or null if not found
     */
	function getFormFieldValue($fieldName, $filter = FILTER_SANITIZE_SPECIAL_CHARS) {
        $value = $this->offsetGet($fieldName);
        if($filter)
            $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
	}

	/**
	 * Clear test request parameters
	 */
	function clearRequestParameters() {
		$this->mTestParameters = array();
	}

	public function offsetExists($offset) {
		return isset($this->mTestParameters[$offset])
			? true
			: parent::offsetExists($offset);
	}

	public function offsetGet($offset) {
		return isset($this->mTestParameters[$offset])
			? $this->mTestParameters[$offset]
			:  parent::offsetGet($offset);
	}

	public function offsetSet($offset, $value) {
		$this->setRequestParameter($offset, $value);
	}

	public function offsetUnset($offset) {
		unset($this->mTestParameters[$offset]);
	}

	/**
	 * Checks to see if a method mock is available
	 * @param $key
	 * @param $callback
	 * @return bool
	 */
	function addMock($key, $callback) {
		if($this->hasMock($key))
			throw new \InvalidArgumentException("Mock already exists: {$key}");
		$this->mMethodMocks[$key] = $callback;
		return $this;
	}

	/**
	 * Checks to see if a method mock is available
	 * @param $key
	 * @return bool
	 */
	function hasMock($key) {
		return isset($this->mMethodMocks[$key]);
	}

	/**
	 * Mock a class method
	 * @param $key
	 * @param $args
	 * @return mixed
	 */
	function mock($key, $args) {
		if(!$this->hasMock($key))
			throw new \InvalidArgumentException("Mock unavailable: {$key}");
		$callback = $this->mMethodMocks[$key];
		return call_user_func_array($callback, $args);
	}
}