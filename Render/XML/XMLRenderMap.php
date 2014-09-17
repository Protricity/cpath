<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\XML;

use CPath\Data\Map\IDataMap;
use CPath\Data\Map\IMappable;
use CPath\Framework\Render\Util\RenderIndents as RI;

class XMLRenderMap implements IDataMap
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
     * @param String $name
     * @param mixed $value
     * @return void
     */
    function mapNamedValue($name, $value) {
        $this->tryStart();
        if($value instanceof IMappable) {
            $Renderer = new XMLRenderMap($name, false);
            $value->mapData($Renderer);

        } else {
            echo RI::ni(), "<", $name, ">", htmlspecialchars($value), "</", $name, ">";
        }
    }

    /**
     * Map a sequential value to this map
     * @param String $value
     * @return void
     */
    function mapValue($value) {
        $this->tryStart();
        if($value instanceof IMappable) {
            $Renderer = new XMLRenderMap($this->mRootElement, false);
            $value->mapData($Renderer);

        } else {
            echo RI::ni(), "<", $this->mRootElement, ">", htmlspecialchars($value), "</", $this->mRootElement, ">";
        }
    }
}