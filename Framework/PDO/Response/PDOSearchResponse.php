<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Response;
use CPath\Data\Map\CallbackKeyMapper;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Framework\Data\Compare\IComparable;
use CPath\Framework\Data\Compare\Util\CompareUtil;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Response\IResponse;
use CPath\Response\IResponse;

class PDOSearchResponse implements IResponse, IKeyMap {
    private $mQuery;

    private $mMessage, $mCode;

    function __construct(PDOSelect $Query, $message = null, $code = null)
    {
        $this->mQuery = $Query;
        $this->mMessage = $message;
        $this->mCode = $code;
    }


    /**
     * Return the PDOSelect query inst
     * @return PDOSelect
     */
    function getQuery() {
        return $this->mQuery;
    }

    /**
     * Compare two objects
     * @param IComparable $obj the object to compare against $this
     * @return integer < 0 if $obj is less than $this; > 0 if $obj is greater than $this, and 0 if they are equal.
     */
    function compareTo(IComparable $obj)
    {
        if(!$obj instanceof PDOSearchResponse)
            return 1;

        $Util = new CompareUtil();
        return $Util->compareScalar(
            $this->mQuery->getSQL(),
            $obj->mQuery->getSQL()
        );
    }

	/**
	 * Map data to a data map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Framework\PDO\Response\IRequest $Request
	 * @internal param \CPath\Framework\PDO\Response\IRequest $Request
	 * @return void
	 */
    function mapKeys(IKeyMapper $Map)
    {
        $Util = new ResponseUtil($this);
        $Util->mapKeys($Map, $this->mQuery);
        if( $this->mQuery->hasDescriptor()) {
            $Descriptor = $this->mQuery->getDescriptor();
            if($Descriptor !== null) {
                if($Descriptor instanceof IKeyMap)
                    $Map->mapSubsection(ISelectDescriptor::JSON_STATS, new CallbackKeyMapper( function(IKeyMapper $Map) use ($Descriptor) {
                        $Descriptor->mapKeys($Map);
                    }));
                else
                    $Map->map(IResponse::JSON_RESPONSE, $Descriptor);
            }
        }
    }

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage()
    {
        return $this->mMessage ?: get_class($this->mQuery);
    }

    /**
     * Get the request status code
     * @return int
     */
    function getCode()
    {
        return $this->mCode ?: IResponse::HTTP_SUCCESS;
    }
}
