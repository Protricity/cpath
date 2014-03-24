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
            $this->fail(null, $label);
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param String $label optional label for thrown exception
     * @return void
     * @throws TestException
     */
    function assertEquals($expected, $actual, $label = null)
    {
        if($expected !== $actual)
            $this->fail("{$expected} !== {$actual}", $label);
    }

    /**
     * @param $reason
     * @param String $label optional label for thrown exception
     * @throws TestException
     */
    function fail($reason, $label = null) {
        throw new TestException(($label ? $label . ' - ' : '') . "Assertion failed: " . $reason);
    }
}