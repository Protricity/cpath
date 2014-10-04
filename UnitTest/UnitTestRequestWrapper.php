<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 4:25 PM
 */
namespace CPath\UnitTest;

use CPath\Request\IRequest;
use CPath\Request\RequestWrapper;

class UnitTestRequestWrapper extends RequestWrapper implements IUnitTestRequest
{
    private $mFlags;

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
        if($condition !== true)
            throw new UnitTestException($message);
    }

}