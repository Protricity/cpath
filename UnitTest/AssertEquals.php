<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 8:56 AM
 */
namespace CPath\UnitTest;

use CPath\UnitTest\Exceptions\UnitTestException;

class AssertEquals implements IUnitTestAssertion
{
	private $mExpected, $mActual;

	public function __construct($expected, $actual) {
		$this->mExpected = $expected;
		$this->mActual   = $actual;
	}

	/**
	 * Assert condition or throws an exception
	 * @param String $message
	 * @return void
	 * @throws UnitTestException
	 */
	function assert($message = null) {
		if (gettype($this->mExpected) !== gettype($this->mActual))
			throw new UnitTestException(($message ? : "Assertion failed") . ": Value types are different");

		if ($this->mExpected !== $this->mActual)
			throw new UnitTestException(($message ? : "Assertion failed") . ": Expected (" . $this->mExpected . ") != Actual (" . $this->mActual . ")");
	}
}