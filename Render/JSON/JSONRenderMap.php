<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\JSON;

use CPath\Data\Map\IDataMap;
use CPath\Data\Map\IMappable;
use CPath\Log;

class JSONRenderMap implements IDataMap
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
     * @param String $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return void
     */
    function mapNamedValue($name, $value) {
        $this->tryStart(false);
        if($this->mIsArray) {
            throw new \InvalidArgumentException(__CLASS__ . " could not map subsection to an array");
        }

        if(in_array($name, $this->mKeyList)) {
            $ex = new \InvalidArgumentException(__CLASS__ . ": duplicate key detected: {$name}");
            //throw new \InvalidArgumentException(__CLASS__ . ": duplicate key detected: {$name}");
            Log::ex(__CLASS__, $ex);
        }
        $this->mKeyList[] = $name;

        if($this->mNextDelim)
            echo $this->mNextDelim;
        echo json_encode($name), ':';

        if($value instanceof IMappable) {
            $this->mNextDelim = null;
            $Renderer = new JSONRenderMap();
            $value->mapData($Renderer);

        } else {
            echo json_encode($value);

        }

        $this->mNextDelim = self::DELIMIT;
    }

    /**
     * Map a sequential value to this map
     * @param String $value
     * @throws \InvalidArgumentException
     * @return void
     */
    function mapValue($value) {
        $this->tryStart(true);
        if(!$this->mIsArray) {
            throw new \InvalidArgumentException(__CLASS__ . " could not array value to an object");
        }
        if($this->mCount)
            echo self::DELIMIT;

        if($value instanceof IMappable) {
            $this->mNextDelim = null;
            $Renderer = new JSONRenderMap();
            $value->mapData($Renderer);
        } else {
            echo json_encode($value);
        }

        $this->mCount++;
    }

}