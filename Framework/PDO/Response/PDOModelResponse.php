<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 4/3/14
 * Time: 8:47 AM
 */
namespace CPath\Framework\PDO\Response;

use CPath\Data\Map\IKeyMapper;
use CPath\Framework\Data\Serialize\Interfaces\ISerializable;
use CPath\Framework\PDO\Table\Model\Interfaces\IPDOModel;
use CPath\Response\IResponse;
use CPath\Response\IResponse;
use CPath\Response\ResponseRenderer;

class PDOModelResponse implements IPDOModel, IResponse
{
    private $mModel, $mMessage, $mCode;

    function __construct(IPDOModel $Model, $message = null, $code = null)
    {
        $this->mModel = $Model;
        $this->mMessage = $message;
        $this->mCode = $code;
    }

    function getModel() {
        return $this->mModel;
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
        $Util = new ResponseRenderer($this);
        $Util->mapKeys($Map, $this->mModel);
    }

    /**
     * @return \CPath\Framework\PDO\Table\Types\PDOTable
     */
    function table()
    {
        return $this->mModel->table();
    }

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage()
    {
        return $this->mMessage ?: $this->mModel;
    }

    /**
     * Get the request status code
     * @return int
     */
    function getCode()
    {
        return $this->mCode ?: IResponse::HTTP_SUCCESS;
    }

    /**
     * EXPORT Object to a simple data structure to be used in var_export($data, true)
     * @return mixed
     */
    function serialize()
    {
        return $this->mModel->serialize();
    }

    /**
     * Unserialize and instantiate an Object with the stored data
     * @param mixed $data the exported data
     * @return ISerializable|Object
     */
    static function unserialize($data)
    {
        // TODO: Implement unserialize() method.
    }
}