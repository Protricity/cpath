<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 8:29 PM
 */
namespace CPath\Render\HTML;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IMappableSequence;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;
use CPath\Framework\Render\Util\RenderIndents as RI;

class HTMLKeyMapRenderer implements IKeyMap
{
    private $mStarted = false;
    private $mAttr;
    private $mRequest;

    public function __construct(IRequest $Request, IAttributes $Attr = null) {
        $this->mRequest = $Request;
        $this->mAttr = $Attr;
    }

    function __destruct() {
        $this->flush();
    }

    private function tryStart() {
        if ($this->mStarted)
            return;

        echo RI::ni(), "<ul", ($this->mAttr ? $this->mAttr->render() : null), ">";
        RI::ai(1);

        $this->mStarted = true;
    }

    public function flush() {
        if (!$this->mStarted)
            return;

        $this->tryStart();

        RI::ai(-1);
        echo RI::ni(), "</ul>";

        $this->mStarted = false;
    }

    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IMappableKeys|IMappableSequence $value
     * @return bool true to stop or any other value to continue
     */
    function map($key, $value) {
        $this->tryStart();
        if(is_array($value))
            $value = new ArraySequence($value);

        if ($value instanceof IMappableKeys) {
            echo RI::ni(), "<li>";
            RI::ai(1);

            echo RI::ni(), "<label>", $key, "</label>";

            $Renderer = new HTMLKeyMapRenderer($this->mRequest);
            $value->mapKeys($Renderer);

            RI::ai(-1);
            echo RI::ni(), "</li>";

        } elseif ($value instanceof IMappableSequence) {
            echo RI::ni(), "<li>";
            RI::ai(1);

            echo RI::ni(), "<label>", $key, "</label>";

            $Renderer = new HTMLSequenceMapRenderer($this->mRequest);
            $value->mapSequence($Renderer);

            RI::ai(-1);
            echo RI::ni(), "</li>";

        } else {
            echo RI::ni(), "<li>";
            RI::ai(1);

            echo RI::ni(), "<label>", $key, "</label>";
            echo RI::ni(), "<span>", htmlspecialchars($value), "</span>";

            RI::ai(-1);
            echo RI::ni(), "</li>";

        }
    }
}

