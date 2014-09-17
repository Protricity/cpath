<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 8:29 PM
 */
namespace CPath\Render\HTML;

use CPath\Data\Map\IDataMap;
use CPath\Data\Map\IMappable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Theme\ITableTheme;
use CPath\Request\IRequest;
use CPath\Framework\Render\Util\RenderIndents as RI;

class HTMLRenderMap implements IDataMap
{
    private $mStarted = false;
    private $mAttr;
    private $mRequest;

    public function __construct(IRequest $Request, IAttributes $Attr=null) {
        $this->mRequest = $Request;
        $this->mAttr = $Attr;
    }


    function __destruct() {
        $this->flush();
    }

    private function tryStart() {
        if($this->mStarted)
            return;

        echo RI::ni(), "<ul", $this->mAttr->render(), ">";
        RI::ai(1);

        $this->mStarted = true;
    }

    public function flush() {
        $this->tryStart();

        RI::ai(-1);
        echo RI::ni(), "</ul>";

        $this->mStarted = false;
    }

    /**
     * Map a sequential value to this map
     * @param String $value
     * @return void
     */
    function mapValue($value) {
        if($value instanceof IMappable) {
            echo RI::ni(), "<li>";
            RI::ai(1);

            $Renderer = new HTMLRenderMap($this->mRequest);
            $value->mapData($Renderer);

            RI::ai(-1);
            echo RI::ni(), "</li>";

        } else {
            echo RI::ni(), "<li>", htmlspecialchars($value), "</li>";

        }
    }

    /**
     * Map data to a key in the map
     * @param String $name
     * @param mixed $value
     * @return void
     */
    function mapNamedValue($name, $value) {
        if($value instanceof IMappable) {
            echo RI::ni(), "<li>";
            RI::ai(1);

            echo RI::ni(), "<label>", $name, "</label>";

            $Renderer = new HTMLRenderMap($this->mRequest);
            $value->mapData($Renderer);

            RI::ai(-1);
            echo RI::ni(), "</li>";

        } else {
            echo RI::ni(), "<li>";
            echo RI::ni(), "<label>", htmlspecialchars($name), "</label>";
            echo RI::ni(), "<span>", htmlspecialchars($value), "</span>";

        }
    }
}