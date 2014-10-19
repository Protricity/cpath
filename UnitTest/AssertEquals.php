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
	private $mExpected, $mActual, $mStrict;

	public function __construct($expected, $actual, $strict=false) {
		$this->mExpected = $expected;
		$this->mActual   = $actual;
		$this->mStrict = $strict;
	}

	/**
	 * Assert condition or throws an exception
	 * @param String $message
	 * @return void
	 * @throws UnitTestException
	 */
	function assert($message = null) {
		if($this->mStrict) {
			if (gettype($this->mExpected) !== gettype($this->mActual))
				throw new UnitTestException(($message ? : "Assertion failed") . ": Value types are different (" . gettype($this->mExpected) . ") != (" . gettype($this->mActual) . ")");
			if ($this->mExpected !== $this->mActual)
				throw new UnitTestException(($message ? : "Assertion failed") . ": Expected (" . $this->mExpected . ") !== Actual (" . $this->mActual . ")");

		} else {
			if ($this->mExpected != $this->mActual)
				throw new UnitTestException(($message ? : "Assertion failed") . ": Expected (" . $this->mExpected . ") != Actual (" . $this->mActual . ")");

		}

	}
}