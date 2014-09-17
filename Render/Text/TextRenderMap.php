<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\Text;

use CPath\Data\Map\IDataMap;
use CPath\Data\Map\IMappable;
use CPath\Framework\Render\Util\RenderIndents as RI;

class TextRenderMap implements IDataMap
{
    private $mStarted = false;

    public function __construct() {

    }

    private function tryStart() {
        if($this->mStarted)
            return;

        $this->mStarted = true;
    }

    /**
     * Map a sequential value to this map
     * @param String $value
     * @return void
     */
    function mapValue($value) {
        if($value instanceof IMappable) {
            $value->mapData($this);

        } else {
            echo RI::ni(), $value;
        }
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
            echo RI::ni(), $name, ": ";
            RI::i(1);
            $value->mapData($this);
            RI::i(-1);

        } else {
            echo RI::ni(), "{$name}: {$value}";
        }
    }
}