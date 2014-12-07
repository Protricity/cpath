<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Response\IResponse;

class ExceptionResponse extends \Exception implements IResponse, IKeyMap {
	const STR_TRACE = 'trace';
	const STR_CLASS = 'class';

    public function __construct(\Exception $ex) {
	    $code = IResponse::HTTP_ERROR;
	    if($ex instanceof IResponse)
		    $code = $ex->getCode();
	    parent::__construct($ex->getMessage(), $code, $ex);
    }

//    /**
//     * Map data to a data map
//     * @param IDataMap $Map the map inst to add data to
//     * @return void
//     */
//    function mapData(IDataMap $Map) {
//        $Util = new ResponseUtil($this);
//        $Util->mapData($Map);
//    }

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		$Ex = $this->getPrevious();
		if($Ex instanceof IKeyMap) {
			$Ex->mapKeys($Map);
		} else {
			$Map->map(IResponse::STR_MESSAGE, $this->getMessage());
			$Map->map(IResponse::STR_CODE, $this->getCode());
			$Map->map(self::STR_CLASS, get_class($Ex));
			$Map->map(self::STR_TRACE, $Ex->getTraceAsString());
		}
	}
}
