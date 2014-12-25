<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/23/2014
 * Time: 7:53 PM
 */
namespace CPath\UnitTest;

interface IUnitTestMockMethod
{
	/**
	 * Checks to see if a method mock is available
	 * @param $key
	 * @param $callback
	 * @return bool
	 */
	function addMock($key, $callback);

	/**
	 * Checks to see if a method mock is available
	 * @param $key
	 * @return bool
	 */
	function hasMock($key);

	/**
	 * Mock a class method
	 * @param $key
	 * @param $args
	 * @return mixed
	 */
	function mock($key, $args);

}