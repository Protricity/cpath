<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 3:33 PM
 */
namespace CPath\UnitTest;

use CPath\Request\IRequest;

interface IUnitTestRequest extends IRequest, IUnitTestMockMethod //, IFlaggedRequest
{
    //const USE_DEFAULTS = 0x2;

	/**
	 * Set a test request parameter for unit test purposes
	 * @param $name
	 * @param $value
	 */
	function setRequestParameter($name, $value);

	/**
	 * Clear test request parameters
	 */
	function clearRequestParameters();

	/**
	 * Assert condition is true or throws an exception
	 * @param bool|IUnitTestAssertion $condition
	 * @param String $message
	 * @return void
	 * @throws \CPath\UnitTest\Exceptions\UnitTestException
	 */
	function assert($condition, $message=null);

	/**
	 * Assert variables are equal or throws an exception
	 * @param String $expected
	 * @param String $actual
	 * @param null $message
	 * @return void
	 */
	function assertEqual($expected, $actual, $message=null);

	/**
	 * Assert a fail condition. Throws a UnitTestException
	 * @param $message
	 * @return mixed
	 * @throws \CPath\UnitTest\Exceptions\UnitTestException
	 */
	function fail($message);
}

