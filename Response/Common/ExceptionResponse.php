<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response\Common;

use CPath\Config;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Request\Exceptions\HTTPRequestException;
use CPath\Response\IResponse;

class ExceptionResponse implements IResponse, IKeyMap {
	const STR_TRACE = 'trace';
    /** @var \Exception */
    private $mEx, $mCode;
    public function __construct(\Exception $ex) {
        $this->mEx = $ex;
        $this->mCode = IResponse::HTTP_ERROR;
        if($ex instanceof HTTPRequestException)
            $this->mCode = $ex->getCode();
    }

//    /**
//     * Map data to a data map
//     * @param IDataMap $Map the map instance to add data to
//     * @return void
//     */
//    function mapData(IDataMap $Map) {
//        $Util = new ResponseUtil($this);
//        $Util->mapData($Map);
//    }

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage() {
        return $this->mEx->getMessage();
    }

    /**
     * Get the DataResponse status code
     * @return int
     */
    function getCode() {
        return $this->mCode;
    }

    function getException() {
        return $this->mEx;
    }

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		if($this->mEx instanceof IKeyMap) {
			$this->mEx->mapKeys($Map);
		} else {
			$Map->map(IResponse::STR_MESSAGE, $this->getMessage());
			$Map->map(IResponse::STR_CODE, $this->getCode());
			$Map->map(self::STR_TRACE, $this->mEx->getTraceAsString());
		}
	}
}
