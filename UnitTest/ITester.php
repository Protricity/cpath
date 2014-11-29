<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/15/14
 * Time: 9:42 AM
 */
namespace CPath\UnitTest;

interface ITester
{
    /**
     * @param bool $condition
     * @param String $label
     * @return void
     * @throws TestException
     */
    function assert($condition, $label = null);

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param String $label
     * @return void
     * @throws TestException
     */
    function assertEquals($expected, $actual, $label = null);
}