<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:48 AM
 */
namespace CPath\Data\Map;

use CPath\Render\HTML\Attribute;
use CPath\Request\IRequest;
use CPath\Response\IResponse;
use CPath\Response\Response;

class MappableResponse extends Response implements IKeyMap
{
    private $mMappable;

    /**
     * Create a new response
     * @param String $msg the response message
     * @param IKeyMap $Mappable
     */
    function __construct($msg, IKeyMap $Mappable) {
        parent::__construct($msg);
        $this->mMappable = $Mappable;
    }

	/**
	 * Map data to a data map
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
    function mapKeys(IKeyMapper $Map) {
//	    $Map->map(IResponse::STR_MESSAGE, $this->getMessage());
//	    $Map->map(IResponse::STR_CODE, $this->getCode());
        $this->mMappable->mapKeys($Map);
    }
}