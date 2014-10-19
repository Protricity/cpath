<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 4:25 PM
 */
namespace CPath\UnitTest;

use CPath\Request\AbstractRequestWrapper;
use CPath\Request\IRequest;
use CPath\UnitTest\Exceptions\UnitTestException;

class UnitTestRequestWrapper extends AbstractRequestWrapper implements IUnitTestRequest
{
    private $mFlags;
	private $mAssertionCount = 0;

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
}