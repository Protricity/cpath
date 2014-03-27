<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Types;

use CPath\Config;
use CPath\Framework\Response\Exceptions\CodedException;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Interfaces\IResponseCode;


class ExceptionResponse implements IResponse {
    /** @var \Exception */
    private $mEx, $mCode;
    public function __construct(\Exception $ex) {
        $this->mEx = $ex;
        $this->mCode = IResponseCode::STATUS_ERROR;
        if($ex instanceof CodedException)
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

}
