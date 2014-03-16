<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/15/14
 * Time: 9:43 AM
 */
namespace CPath\Framework\Test\Common;

use CPath\Framework\Test\Exceptions\TestException;
use CPath\Framework\Test\ITester;

class Tester implements ITester
{

    /**
     * @param bool $condition
     * @param String $label
     * @return void
     * @throws TestException
     */
    function assert($condition, $label = null)
    {
        if(!$condition)
            throw new TestException(($label ? $label . ' - ' : '') . "Assertion failed");
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param String $label
     * @return void
     * @throws TestException
     */
    function assertEquals($expected, $actual, $label = null)
    {
        if($expected !== $actual)
            throw new TestException(($label ? $label . ' - ' : '') . "Assertion failed: {$expected} !== {$actual}");
    }
}