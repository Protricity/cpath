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
use API\DB\Model\ShareEntityModel as SE;
use API\DB\Model\ShareFingerprintModel as SF;
use API\DB\Model\ShareGdriveFolderModel as SGDF;
use API\DB\Model\ShareNetworkHashModel as SNH;
use API\DB\Model\ShareWapEntityModel as SWE;
use API\DB\Model\ShareWapModel as SW;
use API\DB\Model\ShareWapNetworkHashModel as SWNH;
use API\DB\NewAerDB as DB;
use CPath\Framework\Data\Compare\Util\CompareUtil;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Misc\ApiTester as Test;

class BuildTest extends PHPUnit_Framework_TestCase {

    /**
     * Tests Fingerprint matching between WIFI SSIDS
     */
    public function testBuild()
    {
        $Response = Test::cmd('CLI /build');
    }

    /**
     * Tests Fingerprint matching between WIFI in the same network
     */
    public function testMatchNetwork()
    {
        $device1 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice',
            'ispwapid' => 'teste-bssid-abcd;teste-ssid-abcd;52;192.168.0.98;192.168.0.1;255.255.255.0',
        );
        $device2 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice2',
            'ispwapid' => 'teste-bssid-defg;teste-ssid-abcd;52;192.168.0.98;192.168.0.1;255.255.255.0',
        );
        $device3 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice3',
            'ispwapid' => 'teste-bssid-qwer;teste-ssid-abcd;52;192.168.0.98;192.168.0.1;255.255.255.0',
        );
        $oldIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
        $oldHost = isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : NULL;

        // First response creates a share with no matches
        $_SERVER['REMOTE_ADDR'] = '123.234.234.123';
        $_SERVER['REMOTE_HOST'] = '123.234.234.123.host.com';
        $Response1 = Test::cmd('GET /api/entity', $device1);
        $this->assertNotEmpty($Response1['entities']);

        // Second response should match the first response's fingerprint by comparing ip or hostname
        $_SERVER['REMOTE_ADDR'] = '123.234.234.123';
        $_SERVER['REMOTE_HOST'] = '123.234.234.123.host.com';
        $Response2 = Test::cmd('GET /api/entity', $device2);
        $this->assertResponseEqual($Response1, $Response2);

        // Third response should not match the first or second response fingerprint because the host/ip does not match
        $_SERVER['REMOTE_ADDR'] = '123.234.234.111';
        $_SERVER['REMOTE_HOST'] = '123.234.234.123.otherhost.com';
        $Response3 = Test::cmd('GET /api/entity', $device3);
        $this->assertResponseNotEqual($Response1, $Response3);

        $_SERVER['REMOTE_ADDR'] = $oldIP;
        $_SERVER['REMOTE_HOST'] = $oldHost;
    }

    function testMatchBT() {
        $device1 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'iphone;teste-uniqueid-mybtdevice1;teste-btmac-abcd;teste-btname-abcd',
        );
        $device2 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mybtdevice2;teste-btmac-defg;teste-btname-defg',
            'btids' => array(
                ';teste-btmac-abcd;52;;teste-btname-abcd',
            ),
        );

        $Response1 = Test::cmd('GET /api/entity', $device1);    // First device shouldn't return any shares

        $this->assertEmpty($Response1['entities']);

        $Response2 = Test::cmd('GET /api/entity', $device2);    // Second device should create a share with the first device
        $this->assertNotEmpty($Response2['entities']);

        $Response3 = Test::cmd('GET /api/entity', $device1);    // First device should be able to see the share created with it now
        $this->assertNotEmpty($Response3['entities']);

        $JSON = array();
        $Response3->toJSON($JSON);
        $jsonString = json_encode($JSON);
        $this->assertNotNull($JSON);
    }

    function testNoMatchBT() {
        $device1 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'iphone;teste-uniqueid-mybtdevice3;teste-btmac-fghj;teste-btname-fghj',
            'btids' => array(
                ';teste-btmac-dfgh;52;;teste-btname-dfgh',
            ),
        );
        $device2 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mybtdevice4;teste-btmac-rtyu;teste-btname-rtyu',
            'btids' => array(
                ';teste-btmac-erty;52;;teste-btname-erty',
            ),
        );

        $Response1 = Test::cmd('GET /api/entity', $device1);    // First device should return empty
        $this->assertEmpty($Response1['entities']);

        $Response2 = Test::cmd('GET /api/entity', $device2);    // Second device should return empty as well
        $this->assertEmpty($Response2['entities']);
    }

    function testWifiToBT() {
        $device1 = 'mydevice=iphone;40987a322e61193020b0a8df2767f67a;;ipadmini&ispwapid=08ea44377e2b%3BCrossCamp%2EusMembers%3B99&appid=' . self::APP_ID;
        $device2 = 'mydevice=iphone;c3b8e85aeead639fca9b780b72e25832;;eatsrcks&btids=40987a322e61193020b0a8df2767f67a%3B%3B99%3Biphone%3Bipadmini&appid=' . self::APP_ID;

        parse_str($device1, $device1);
        parse_str($device2, $device2);

        $Response1 = Test::cmd('GET /api/entity', $device1);    // First device should create a new entity
        $this->assertNotEmpty($Response1['entities']);

        $Response2 = Test::cmd('GET /api/entity', $device2);    // Second device should match the entity
        $this->assertNotEmpty($Response2['entities']);
    }


    function testWifiOrder() {
        $device = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice',
            'wapids' => array(
                'ateste-bssid-abcd;ateste-ssid-abcd;14',
                'bteste-bssid-defg;bteste-ssid-defg;54',
                'cteste-bssid-hijk;cteste-ssid-hijk;23',
            ),
        );

        $Response1 = Test::cmd('GET /api/entity', $device);
        $this->assertStringStartsWith('bteste-ssid-defg', $Response1['entities'][0]->getName());

        $device = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice',
            'wapids' => array(
                'dteste-bssid-defg;teste-ssid-defg;38',
                'eteste-bssid-abcd;teste-ssid-abcd;12',
                'fteste-bssid-hijk;teste-ssid-abcd;23', // These two should combine with more weight than the first
            ),
        );


        $Response2 = Test::cmd('GET /api/entity', $device);
        $this->assertStringStartsWith('teste-ssid', $Response2['entities'][0]->getName());
    }


    function testNewEntity() {
        $install = 'android;teste3-uniqueid-mywapdevice';
        $device = array ( 'appid' => self::APP_ID,
            'mydevice' => $install,
            'wapids' => array(
                'ateste3-bssid-abcd;ateste-ssid-abcd;14',
                'bteste3-bssid-defg;bteste-ssid-defg;54',
                'cteste3-bssid-hijk;cteste-ssid-hijk;23',
            ),
        );

        $Response1 = Test::cmd('GET /api/entity', $device);    // Device should create and find an entity
        $this->assertNotEmpty($Response1['entities']);
        $this->assertEquals(1, count($Response1['entities']));


        $device = array ( 'appid' => self::APP_ID,
            'mydevice' => $install,
            'wapids' => array(
                'dteste3-bssid-abcd;dteste-ssid-abcd;14',
                'eteste3-bssid-defg;eteste-ssid-defg;54',
                'fteste3-bssid-hijk;fteste-ssid-hijk;23',
            ),
        );

        $Response2 = Test::cmd('GET /api/entity', $device);    // Device should not find the same entity
        $this->assertNotEmpty($Response2['entities']);
        $this->assertEquals(2, count($Response2['entities']));

        SDE::update(SDE::LastSeen)
            ->whereSQL("true")
            ->values(time() - (\API\Model\ShareEntity::DEVICE_TIME_LIMIT + 1));

        $Response3 = Test::cmd('GET /api/entity', $device);    // Device should not find the same entity
        $this->assertNotEmpty($Response3['entities']);
        $this->assertEquals(1, count($Response3['entities']));
    }


    function testEntityLimit() {
        $device = array();
        for($i=0; $i<4; $i++) {
            $device[$i] = array ( 'appid' => self::APP_ID,
                'mydevice' => "android;testel-uniqueid-mywapdevice{$i}",
            );

            $inc = $i*3;
            for($j=$inc; $j<10+$inc; $j++) {
                $device[$i]['wapids'][] = "testel-bssid-{$j}abcd;testel-ssid-{$j}abcd;{$j}";
            }
            $Response1 = Test::cmd('GET /api/entity', $device[$i]);
            $this->assertGreaterThanOrEqual(1, count($Response1['entities'][0]->getActiveWAPs()));
            $this->assertGreaterThanOrEqual(1, count($Response1['entities'][0]->getActiveDevices()));
        }

    }


    function testInstallLastSeen() {
        $install = 'android;testls1-uniqueid-mywapdevice';
        $device = array ( 'appid' => self::APP_ID,
            'mydevice' => $install,
            'wapids' => array(
                'ateste3-bssid-abcd;ateste-ssid-abcd;14',
            ),
        );

        $Response1 = Test::cmd('GET /api/entity', $device);    // Device should create and find an entity
        $this->assertNotEmpty($Response1['entities']);
        /** @var \API\Model\ShareEntity $Entity */
        $Entity = $Response1['entities'][0];


        $Devices = $Entity->getActiveDevices();
        /** @var \API\Model\ShareDevice $Device */
        $Device = $Devices[0];

        /** @var \API\DB\Model\ShareInstallModel $Install */
        //$Install = $Device->getInstallInstance();
        //$this->assertNotEmpty($Install->getLastSeen()); // Does not apply here
    }


    function testDuplicateISPWAP() {
        $device1 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice',
            'wapids' => array(
                '001e5279d089;Sluggardy;56,0026f37474d8;OAKCREEKAPTS306;44,20aa4b485673;SUOC1788-310;35,a021b788824e;BONE-2.4G;11,b88d125f0931;Baguette;20,48f8b332bed4;SUOC1788-409;0,002493475960;Stanford209;26,b88d125f0932;Baguette;14,94ccb9d8b850;ATT152;17,5c571a21c090;HOME-C092;0,20aa4bc63375;SUOC1788-209;0,4432c85b924c;HOME-924C;11',
            ),
            'ispwapid' => '001e5279d089;Sluggardy;52;10.0.1.9;255.255.255.0;10.0.1.1',
        );

        $oldIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
        $oldHost = isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : NULL;
        $_SERVER['REMOTE_ADDR'] = '123.234.234.123';
        $_SERVER['REMOTE_HOST'] = '123.234.234.123.host.com';

        $Response1 = Test::cmd('GET /api/entity', $device1);
        $Response1 = Test::cmd('GET /api/entity', $device1);

        $_SERVER['REMOTE_ADDR'] = $oldIP;
        $_SERVER['REMOTE_HOST'] = $oldHost;
    }


    function testBlindProximity() {
        $device1 = '/api/entity?mydevice=iphone;366a9359de0f0db5e2d5883e874ac66c;;eatsrcks&btids=b129e737ced9728b336e8032107ecab2;;99;iphone;eatsrcksipodtouch&wapids=d38c54b20e5bf51d8537c26a2612c610;eatsrcksguest;99&ispwapid=d38c54b20e5bf51d8537c26a2612c610;eatsrcksguest;99;;';
        $device2 = '/api/entity?mydevice=iphone;b129e737ced9728b336e8032107ecab2;;eatsrcksipodtouch&btids=366a9359de0f0db5e2d5883e874ac66c;;99;iphone;eatsrcks&wapids=9d070bcc91ff27c9721f4690c51e7da1;eatsrcksg;99&ispwapid=9d070bcc91ff27c9721f4690c51e7da1;eatsrcksg;99;;';

        $device1 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'iphone;366a9359de0f0db5e2d5883e874ac66c;;eatsrcks',
            'btids' => array(
                'b129e737ced9728b336e8032107ecab2;;99;iphone;eatsrcksipodtouch',
            ),
            'wapids' => array(
                'd38c54b20e5bf51d8537c26a2612c610;eatsrcksguest;99',
            ),
            'ispwapid' => 'd38c54b20e5bf51d8537c26a2612c610;eatsrcksguest;99;;',
        );
        $device2 = array ( 'appid' => self::APP_ID,
            'mydevice' => 'iphone;b129e737ced9728b336e8032107ecab2;;eatsrcksipodtouch',
            'btids' => array(
                '366a9359de0f0db5e2d5883e874ac66c;;99;iphone;eatsrcks',
            ),
            'wapids' => array(
                '9d070bcc91ff27c9721f4690c51e7da1;eatsrcksg;99',
            ),
            'ispwapid' => '9d070bcc91ff27c9721f4690c51e7da1;eatsrcksg;99;;',
        );

        $Response1 = Test::cmd('GET /api/entity', $device1);
        $this->assertEquals(1, count($Response1['entities']));
        $Response2 = Test::cmd('GET /api/entity', $device2);
        $this->assertEquals(2, count($Response2['entities']));
        $Response3 = Test::cmd('GET /api/entity', $device1);
        $this->assertEquals(2, count($Response3['entities']));
        // TODO: FINISH
    }


    function testSearching() {
        $device = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice',
            'wapids' => array(
                'ateste-bssid-abcd;ateste-ssid-abcd;14',
                'bteste-bssid-defg;bteste-ssid-defg;54',
                'cteste-bssid-hijk;cteste-ssid-hijk;23',
            ),
        );

        $Response1 = Test::cmd('GET /api/entity', $device);
        $this->assertStringStartsWith('bteste-ssid-defg', $Response1['entities'][0]->getName());

        $device = array ( 'appid' => self::APP_ID,
            'mydevice' => 'android;teste-uniqueid-mywapdevice',
            'wapids' => array(
                'dteste-bssid-defg;teste-ssid-defg;38',
                'eteste-bssid-abcd;teste-ssid-abcd;12',
                'fteste-bssid-hijk;teste-ssid-abcd;23', // These two should combine with more weight than the first
            ),
        );

        $Response2 = Test::cmd('GET /api/entity', $device);
        $this->assertStringStartsWith('teste-ssid', $Response2['entities'][0]->getName());
    }


    private static function prepareDB() {
        static $prepared = false;
        if($prepared)
            return;
        $prepared = true;
        DB::getTest();                              // Use the Test Database
        DB::get()->setDBVersion(0)->upgrade();
        $t = array(                                 // Get all the tables we want to truncate
            SF::TABLE,
            SE::TABLE,
            SD::TABLE,
            SW::TABLE,
            SDE::TABLE,
            SWE::TABLE,
            SGDF::TABLE,
            SWNH::TABLE,
            SNH::TABLE,
        );
        //$SQL = '';
        //foreach($t as $tt) {
            //$SQL = 'TRUNCATE '.$tt.' CASCADE;';   // Truncate Cascade each table
            //DB::get()->exec($SQL);
        //}
                                                            // Verify that the current database has 'test' in it
        if(strpos(DB::get()->query('select current_database()')->fetchColumn(0), 'test') === false)
            throw new \Exception("Database name does not contain 'test'");

    }

    private function assertResponseEqual(DataResponse $R1, DataResponse $R2) {
        $R1->compareTo($R2, new \CPath\Framework\Data\Compare\Util\CompareUtil());
    }

    private function assertResponseNotEqual(DataResponse $R1, DataResponse $R2) {
        try{
            $R1->compareTo($R2, new CompareUtil());
            $this->fail("Responses are equal");
        } catch (\CPath\Compare\NotEqualException $ex) {

        }
    }
}
