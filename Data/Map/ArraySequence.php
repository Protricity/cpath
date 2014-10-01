<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 6:15 PM
 */
namespace CPath\Data\Map;

use CPath\Response\Response;

class ArraySequence implements ISequenceMap
{
    private $mArr;

    public function __construct(Array $array) {
        $this->mArr = $array;
    }

    /**
     * Map sequential data to the map
     * @param IMappableSequence $Map
     * @internal param \CPath\Data\Map\IRequest $Request
     * @return mixed
     */
    function mapSequence(IMappableSequence $Map) {
        foreach ($this->mArr as $value)
            $Map->mapNext($value);
    }
}