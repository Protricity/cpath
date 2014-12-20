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
use CPath\UnitTest\Exceptions\UnitTestException;

class UnitTestRequestWrapper extends AbstractRequestWrapper implements IUnitTestRequest, ISessionRequest, IFormRequest
{
    private $mFlags;
	private $mAssertionCount = 0;
	private $mTestSession = array();
	private $mTestParameters = array();

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
//		throw new \InvalidArgumentException("Wrapped requests does not implement ISessionRequest");
	}

	/**
	 * Start a new session
	 * @param bool $reset if true, session will be reset
	 * @throws \InvalidArgumentException
	 * @return bool true if session was started, otherwise false
	 */
	function startSession($reset = false) {
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
	 * @return mixed|null the form field value or null if not found
	 */
	function getFormFieldValue($fieldName) {
		return $this->offsetGet($fieldName);
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
}