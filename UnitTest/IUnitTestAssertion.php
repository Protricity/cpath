<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 8:56 AM
 */
namespace CPath\UnitTest;

use CPath\UnitTest\Exceptions\UnitTestException;

interface IUnitTestAssertion
{
	/**
	 * Assert condition or throws an exception
	 * @param String $message
	 * @return void
	 * @throws UnitTestException
	 */
	function assert($message = null);
}