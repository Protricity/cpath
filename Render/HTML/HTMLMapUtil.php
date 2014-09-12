<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\HTML;

use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Render\Util\RenderIndents as RI;

class HTMLMapUtil implements IDataMap
{
    private $mStarted = false;

    public function __construct() {

    }

    function __destruct() {
        if($this->mStarted)
            $this->stop();
    }

    public function start() {
        if($this->mStarted)
            throw new \InvalidArgumentException(__CLASS__ . " was already started");
        $this->mStarted = true;
    }

    public function stop() {
        if(!$this->mStarted)
            throw new \InvalidArgumentException(__CLASS__ . " was not started");
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
        echo RI::ni(), "{$key}: {$value}";
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

        echo RI::ni(), "{$subsectionKey}: ";
        RI::i(1);
        $Mappable->mapData($this);
        RI::i(-1);
    }

    /**
     * Map an object to this array
     * @param IMappable $Mappable
     * @return void
     */
    function mapArrayObject(IMappable $Mappable)
    {
        $Mappable->mapData($this);
    }

    /**
     * Add a value to the array
     * @param mixed $value
     * @return void
     */
    function mapArrayValue($value)
    {
        echo RI::ni(), $value;
    }

    // Static

    static function renderMap(IMappable $Map) {
        $Renderer = new HTMLMapUtil();
        $Renderer->start();
        $Map->mapData($Renderer);
        $Renderer->stop();
    }
}