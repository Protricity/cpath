<?php
/**
 * Project: newaer-server-share
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 6/17/13
 * Time: 8:04 PM
 */

include_once __DIR__ . '/../Base.php';

use API\DB\Model\ShareDeviceEntityModel as SDE;
use API\DB\Model\ShareDeviceModel as SD;
use API\DB\Model\ShareWapEntityModel as SWE;
use API\DB\Model\ShareWapModel as SW;
use API\DB\Model\ShareWapNetworkHashModel as SWNH;
use CPath\Misc\ApiTester as Test;

class BuildTest extends PHPUnit_Framework_TestCase {

    public function testBuild()
    {
        $Response = Test::cmd('CLI /build');
    }

}
