<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Map\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IMappableSequence;
use CPath\Data\Map\ISequenceMap;

class MappableKeysCallback implements IMappableKeys {
    //const IS_FIRST = 0x01;

    private $mCallback, $mCount=0;

    function __construct($callback) {
        $this->mCallback = $callback;
    }

    /**
     * Map data to a data map
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map) {
        $call = $this->mCallback;
        $call($Map);
    }
}


