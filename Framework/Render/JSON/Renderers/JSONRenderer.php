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

class JSONRenderer implements IDataMap
{
    const DELIMIT = ', ';

    private $mStarted = false, $mNextDelim=null;
    private $mCount = 0;


    public function __construct() {

    }

    function __destruct() {
        if($this->mStarted)
            $this->stop();
    }

    public function start() {
        if($this->mStarted)
            throw new \InvalidArgumentException(__CLASS__ . " was already started");
        echo '{';
        $this->mStarted = true;
    }

    public function stop() {
        if(!$this->mStarted)
            throw new \InvalidArgumentException(__CLASS__ . " was not started");
        echo '}';
        $this->mStarted = false;
    }

    /**
     * Map data to a key in the map
     * @param String $key
     * @param mixed|Callable $value
     * @param int $flags
     * @return void
     */
    function mapKeyValue($key, $value, $flags = 0)
    {
        if(!$this->mStarted)
            $this->start();
        if($this->mNextDelim)
            echo $this->mNextDelim;
        echo json_encode($key), ':', json_encode($value);
        $this->mNextDelim = self::DELIMIT;
    }

    /**
     * Map data to subsection
     * @param $subsectionKey
     * @param IMappable $Mappable
     * @return void
     */
    function mapSubsection($subsectionKey, IMappable $Mappable)
    {
        if(!$this->mStarted)
            $this->start();
        if($this->mNextDelim)
            echo $this->mNextDelim;
        echo json_encode($subsectionKey), ':{';
        $this->mNextDelim = null;

        $c = $this->mCount; $this->mCount = 0;
        $Mappable->mapData($this);
        $this->mCount = $c;

        echo '}';
        $this->mNextDelim = self::DELIMIT;
    }

    /**
     * Map an object to this array
     * @param IMappable $Mappable
     * @return void
     */
    function mapArrayObject(IMappable $Mappable)
    {
        if($this->mCount)
            echo self::DELIMIT;
        $Mappable->mapData($this);
        $this->mCount++;
    }

    /**
     * Add a value to the array
     * @param mixed $value
     * @return void
     */
    function mapArrayValue($value)
    {
        if($this->mCount)
            echo self::DELIMIT;
        echo json_encode($value);
        $this->mCount++;
    }

    // Static

    static function renderMap(IMappable $Map) {
        $Renderer = new JSONRenderer();
        $Renderer->start();
        $Map->mapData($Renderer);
        $Renderer->stop();
    }
}