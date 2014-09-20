<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 12:58 PM
 */
namespace CPath\Data\Map;

interface IMappableSequence
{
    /**
     * Map sequential data to the map
     * @param ISequenceMap $Map
     * @return mixed
     */
    function mapSequence(ISequenceMap $Map);
}

