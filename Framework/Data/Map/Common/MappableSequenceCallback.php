<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 1:16 AM
 */
namespace CPath\Framework\Data\Map\Common;

use CPath\Data\Map\IMappableSequence;
use CPath\Data\Map\ISequenceMap;

class MappableSequenceCallback implements IMappableSequence
{
    //const IS_FIRST = 0x01;

    private $mCallback;

    function __construct($callback) {
        $this->mCallback = $callback;
    }

    /**
     * Map sequential data to the map
     * @param ISequenceMap $Map
     * @return mixed
     */
    function mapSequence(ISequenceMap $Map) {
        $call = $this->mCallback;
        $call($Map);
    }
}