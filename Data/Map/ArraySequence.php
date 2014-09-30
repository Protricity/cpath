<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 6:15 PM
 */
namespace CPath\Data\Map;

use CPath\Response\Response;

class ArraySequence implements IMappableSequence
{
    private $mArr;

    public function __construct(Array $array) {
        $this->mArr = $array;
    }

    /**
     * Map sequential data to the map
     * @param ISequenceMap $Map
     * @return mixed
     */
    function mapSequence(ISequenceMap $Map) {
        foreach ($this->mArr as $value)
            $Map->mapNext($value);
    }
}