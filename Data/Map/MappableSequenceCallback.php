<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:29 PM
 */
namespace CPath\Data\Map;

class MappableSequenceCallback implements ISequenceMap
{
    private $mCallback;

    function __construct(\Closure $callback) {
        $this->mCallback = $callback;
    }

    /**
     * Map sequential data to the map
     * @param ISequenceMapper $Map
     * @internal param \CPath\Framework\Data\Map\Common\IRequest $Request
     * @return void
     */
    function mapSequence(ISequenceMapper $Map) {
        $call = $this->mCallback;
        $call($Map);
    }

}