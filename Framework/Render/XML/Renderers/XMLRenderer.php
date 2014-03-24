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

    public function __construct($rootElementName='root', $declaration=false) {
        $this->mRootElement = $rootElementName;
        $this->mDeclaration = $declaration;
    }

    function __destruct() {
        $this->flush();
    }

    private function tryStart() {
        if($this->mStarted)
            return;
            //throw new \InvalidArgumentException(__CLASS__ . " was already started");

        if($this->mDeclaration === true)
            echo "<?xml version='1.0' encoding='UTF-8'?>", RI::ni();
        elseif(is_string($this->mDeclaration))
            echo $this->mDeclaration, RI::ni();

        echo "<", $this->mRootElement, ">";
        RI::ai(1);

        $this->mStarted = true;
    }

    public function flush() {
        $this->tryStart();

        RI::ai(-1);
        echo RI::ni(), "</", $this->mRootElement, ">";

        $this->mStarted = false;
    }

    /**
     * Map data to a key in the map
     * @param String $key
     * @param mixed|Callable $value
     * @param int $flags
     * @return void
     */
    function mapKeyValue($key, $value, $flags = 0) {
        $this->tryStart();
        echo RI::ni(), "<", $key, ">", htmlspecialchars($value), "</", $key, ">";
    }

    /**
     * Map data to subsection
     * @param $subsectionKey
     * @param IMappable $Mappable
     * @return void
     */
    function mapSubsection($subsectionKey, IMappable $Mappable) {
        $this->tryStart();
        //echo RI::ni(), "<", $subsectionKey, ">";
        //RI::ai(1);
        XMLRenderer::renderMap($Mappable, $subsectionKey, false);
//        $Renderer = new XMLRenderer($subsectionKey, false);
//        $Mappable->mapData($Renderer);
        //RI::ai(-1);
        //echo RI::ni(), "</", $subsectionKey, ">";
    }

    /**
     * Map an object to this array
     * @param IMappable $Mappable
     * @return void
     */
    function mapArrayObject(IMappable $Mappable) {
        $this->tryStart();
        XMLRenderer::renderMap($Mappable, $this->mRootElement, false);
    }

    /**
     * Add a value to the array
     * @param mixed $value
     * @return void
     */
    function mapArrayValue($value) {
        $this->tryStart();
        echo RI::ni(), "<", $this->mRootElement, ">", htmlspecialchars($value), "</", $this->mRootElement, ">";
    }

    // Static

    static function renderMap(IMappable $Map, $rootElementName='root', $declaration=false) {
        $Renderer = new XMLRenderer($rootElementName, $declaration);
        $Map->mapData($Renderer);
    }
}