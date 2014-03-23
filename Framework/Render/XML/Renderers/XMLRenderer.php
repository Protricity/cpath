<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Framework\Render\XML\Renderers;

use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Render\Util\RenderIndents as RI;

class XMLRenderer implements IDataMap
{
    private $mStarted = false;
    private $mRootElement, $mDeclaration;

    public function __construct($rootElementName='root', $declaration=true) {
        $this->mRootElement = $rootElementName;
        $this->mDeclaration = $declaration;
    }

    function __destruct() {
        if($this->mStarted)
            $this->stop();
    }

    public function start() {
        static $declared = false;
        if($this->mStarted)
            throw new \InvalidArgumentException(__CLASS__ . " was already started");

        if(!$declared) {
            if($this->mDeclaration === true)
                echo "<?xml version='1.0' encoding='UTF-8'?>";
            elseif(is_string($this->mDeclaration))
                echo $this->mDeclaration;
            $declared = true;
        }

        echo RI::ni(), "<", $this->mRootElement, ">";
        RI::ai(1);

        $this->mStarted = true;
    }

    public function stop() {
        if(!$this->mStarted)
            throw new \InvalidArgumentException(__CLASS__ . " was not started");

        RI::ai(-1);
        echo RI::ni(), "<\\", $this->mRootElement, ">";

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
        echo RI::ni(), "<", $key, ">", htmlspecialchars($value), "</", $key, ">";
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
        //echo RI::ni(), "<", $subsectionKey, ">";
        //RI::ai(1);
        XMLRenderer::renderMap($Mappable, $subsectionKey);
//        $Renderer = new XMLRenderer($subsectionKey, false);
//        $Mappable->mapData($Renderer);
        //RI::ai(-1);
        //echo RI::ni(), "<\\", $subsectionKey, ">";
    }

    /**
     * Map an object to this array
     * @param IMappable $Mappable
     * @return void
     */
    function mapArrayObject(IMappable $Mappable)
    {
        if(!$this->mStarted)
            $this->start();

        $Renderer = new XMLRenderer($this->mRootElement);
        $Mappable->mapData($Renderer);
    }

    /**
     * Add a value to the array
     * @param mixed $value
     * @return void
     */
    function mapArrayValue($value)
    {
        if(!$this->mStarted)
            $this->start();
        echo RI::ni(), "<", $this->mRootElement, ">", htmlspecialchars($value), "</", $this->mRootElement, ">";
    }

    // Static

    static function renderMap(IMappable $Map, $rootElementName='root') {
        $Renderer = new XMLRenderer($rootElementName);
        $Renderer->start();
        $Map->mapData($Renderer);
        $Renderer->stop();
    }
}