<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 3:33 PM
 */
namespace CPath\UnitTest;

use CPath\Request\IRequest;

interface IUnitTestRequest extends IRequest //, IFlaggedRequest
{
    //const USE_DEFAULTS = 0x2;
    /**
     * Assert condition is true or throws an exception
     * @param bool|IUnitTestAssertion $condition
     * @param String $message
     * @return void
     * @throws \CPath\UnitTest\Exceptions\UnitTestException
     */
    function assert($condition, $message=null);
}

