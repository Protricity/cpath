<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 12:58 PM
 */
namespace CPath\Data\Map;

interface ISequenceMap
{
    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IMappableKeys|IMappableSequence $value
     * @return bool false to continue, true to stop
     */
    function mapNext($value);
}