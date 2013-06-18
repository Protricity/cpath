<?php
/**
 * Project: newaer-server-share
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 6/17/13
 * Time: 8:04 PM
 */

include('../base.class.php');
use CPath\Util;
use CPath\Base;
class UtilTest extends PHPUnit_Framework_TestCase {

    public function __construct() {
        Base::load();
    }

    public function testCLI()
    {
        $_SERVER['argv'] = array('', 'GET', '/my/path');
        Util::init();
        $this->assertEquals(Util::getUrl('method'), 'GET');
        $this->assertEquals(Util::getUrl('path'), '/my/path');
    }

    public function testJSON() {
        $json = Util::toJSON(array('key'=>'val','arr'=>array('key2'=>'val2')));
    }

    public function testXML() {
        $XML = Util::toXML(array('key'=>'val','arr'=>array('key2'=>'val2')));
        $this->assertTrue($XML instanceof \SimpleXMLElement);
    }
}
