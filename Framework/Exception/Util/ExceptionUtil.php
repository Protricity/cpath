<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 12:33 PM
 */
namespace CPath\Framework\Exception\Util;

use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Render\Common\MapRenderer;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\IRenderAggregate;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Interfaces\IResponseCode;

class ExceptionUtil extends \Exception implements IMappable, IRenderAggregate, IResponse
{
    public function __construct(\Exception $ex)
    {
        if($ex instanceof IResponseCode)
            $code = $ex->getCode();
        else
            $code = IResponseCode::STATUS_ERROR;
        parent::__construct($ex->getMessage(), $code, $ex);
    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map)
    {
        $Ex = $this->getPrevious();
        if($Ex instanceof IMappable) {
            $Ex->mapData($Map);
        } else {
            $Map->mapKeyValue('message', $Ex->getMessage());
            $Map->mapKeyValue('file', basename($Ex->getFile()) . ':' . $Ex->getLine());
            //$Map->mapKeyValue('code', $Ex->getCode());
        }
    }


    /**
     * @return IRender
     */
    function getRenderer() {
        return new MapRenderer($this);
    }
}