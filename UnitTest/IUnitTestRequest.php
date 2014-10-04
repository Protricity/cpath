<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 3:33 PM
 */
namespace CPath\UnitTest;

use CPath\Request\IRequest;

class UnitTestException extends \Exception {

}

interface IUnitTestRequest extends IRequest //, IFlaggedRequest
{
    //const USE_DEFAULTS = 0x2;
    /**
     * Assert condition is true or throws an exception
     * @param bool|IUnitTestAssertion $condition
     * @param String $message
     * @return void
     * @throws UnitTestException
     */
    function assert($condition, $message=null);
}

interface IUnitTestAssertion {
    /**
     * Assert condition or throws an exception
     * @param String $message
     * @return void
     * @throws UnitTestException
     */
    function assert($message=null);
}

class AssertEquals implements IUnitTestAssertion {
    private $mExpected, $mActual;
    public function __construct($expected, $actual) {
        $this->mExpected = $expected;
        $this->mActual = $actual;
    }

    /**
     * Assert condition or throws an exception
     * @param String $message
     * @return void
     * @throws UnitTestException
     */
    function assert($message = null) {
        if(gettype($this->mExpected) !== gettype($this->mActual))
            throw new UnitTestException(($message ?: "Assertion failed") . ": Value types are different");

        if($this->mExpected !== $this->mActual)
            throw new UnitTestException(($message ?: "Assertion failed") . ": Expected (" . $this->mExpected . ") != Actual (" . $this->mActual . ")");
    }
}