<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:48 AM
 */
namespace CPath\Data\Map;

use CPath\Response\Response;

class MappableResponse extends Response implements IMappableKeys
{
    private $mMappable;

    /**
     * Create a new response
     * @param String $msg the response message
     * @param IMappableKeys $Mappable
     */
    function __construct($msg, IMappableKeys $Mappable) {
        parent::__construct($msg);
        $this->mMappable = $Mappable;
    }

    /**
     * Map data to a data map
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map) {
        $this->mMappable->mapKeys($Map);
    }
}