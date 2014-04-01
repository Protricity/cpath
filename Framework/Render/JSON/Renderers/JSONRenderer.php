<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Framework\Render\JSON\Renderers;

use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Log;

class JSONRenderer implements IDataMap
{
    const DELIMIT = ', ';

    private $mStarted = false, $mNextDelim=null, $mIsArray = false;
    private $mCount = 0;
    private $mKeyList = array();


    public function __construct() {

    }

    function __destruct() {
        $this->flush();
    }

    private function tryStart($isArray) {
        if(!$this->mStarted) {
            $this->mStarted = true;
            $this->mIsArray = $isArray;
            echo $isArray ? '[' : '{';
        }
    }

    public function flush() {
        if(!$this->mStarted) {
            echo '{}';
            $this->mStarted = false;
            return;
            //throw new \InvalidArgumentException(__CLASS__ . " was not started");
        }
        if($this->mIsArray)
            echo ']';
        else
            echo '}';
        $this->mStarted = false;
    }

    /**
     * Map data to a key in the map
     * @param String $key
     * @param mixed|Callable $value
     * @param int $flags
     * @throws \InvalidArgumentException
     * @return void
     */
    function mapKeyValue($key, $value, $flags = 0)
    {
        $this->tryStart(false);
        if($this->mIsArray) {
            throw new \InvalidArgumentException(__CLASS__ . " could not map subsection to an array");
        }

        if(in_array($key, $this->mKeyList)) {
            $ex = new \InvalidArgumentException(__CLASS__ . ": duplicate key detected: {$key}");
            //throw new \InvalidArgumentException(__CLASS__ . ": duplicate key detected: {$key}");
            Log::ex(__CLASS__, $ex);
        }
        $this->mKeyList[] = $key;

        if($this->mNextDelim)
            echo $this->mNextDelim;
        echo json_encode($key), ':', json_encode($value);
        $this->mNextDelim = self::DELIMIT;
    }

    /**
     * Map data to subsection
     * @param $subsectionKey
     * @param IMappable $Mappable
     * @throws \InvalidArgumentException
     * @return void
     */
    function mapSubsection($subsectionKey, IMappable $Mappable)
    {
        $this->tryStart(false);
        if($this->mIsArray) {
            throw new \InvalidArgumentException(__CLASS__ . " could not map subsection to an array");
        }

        if($this->mNextDelim)
            echo $this->mNextDelim;
        echo json_encode($subsectionKey), ':';
        $this->mNextDelim = null;

        JSONRenderer::renderMap($Mappable);

        $this->mNextDelim = self::DELIMIT;
    }

    /**
     * Map an object to this array
     * @param IMappable $Mappable
     * @throws \InvalidArgumentException
     * @return void
     */
    function mapArrayObject(IMappable $Mappable)
    {
        $this->tryStart(true);
        if(!$this->mIsArray) {
            throw new \InvalidArgumentException(__CLASS__ . " could not array value to an object");
        }
        if($this->mCount)
            echo self::DELIMIT;
        JSONRenderer::renderMap($Mappable);
        $this->mCount++;
    }

    /**
     * Add a value to the array
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return void
     */
    function mapArrayValue($value)
    {
        $this->tryStart(true);
        if(!$this->mIsArray) {
            throw new \InvalidArgumentException(__CLASS__ . " could not array value to an object");
        }
        if($this->mCount)
            echo self::DELIMIT;
        echo json_encode($value);
        $this->mCount++;
    }

    // Static

    static function renderMap(IMappable $Map) {
        $Renderer = new JSONRenderer();
        $Map->mapData($Renderer);
    }
}