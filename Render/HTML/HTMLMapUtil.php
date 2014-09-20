<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\HTML;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;
use CPath\Framework\Render\Util\RenderIndents as RI;

class HTMLMapUtil implements IKeyMap
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
     * @param IMappableKeys $Mappable
     * @return void
     */
    function mapSubsection($subsectionKey, IMappableKeys $Mappable)
    {
        if(!$this->mStarted)
            $this->start();

        echo RI::ni(), "{$subsectionKey}: ";
        RI::i(1);
        $Mappable->mapKeys($this);
        RI::i(-1);
    }

    /**
     * Map an object to this array
     * @param IMappableKeys $Mappable
     * @return void
     */
    function mapArrayObject(IMappableKeys $Mappable)
    {
        $Mappable->mapKeys($this);
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

    static function renderMap(IMappableKeys $Map) {
        $Renderer = new HTMLMapUtil();
        $Renderer->start();
        $Map->mapKeys($Renderer);
        $Renderer->stop();
    }
}