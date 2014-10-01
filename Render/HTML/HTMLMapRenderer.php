<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/16/14
 * Time: 8:29 PM
 */
namespace CPath\Render\HTML;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\IMappableSequence;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\ClassAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\URL\IHasURL;
use CPath\Request\IRequest;
use CPath\Framework\Render\Util\RenderIndents as RI;

class HTMLMapRenderer implements IMappableKeys, IMappableSequence, IHTMLSupportHeaders
{
    const CSS_CLASS = 'html-map-renderer';
    const CSS_CLASS_SEQUENCE_ITEM = 'sequence-item';
    const CSS_CLASS_MAP_KEY_PAIR = 'key-map-pair';

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

        $Attr = new ClassAttributes(self::CSS_CLASS);
        $Attr = $Attr->merge($this->mAttr);

        echo RI::ni(), "<ul", $Attr, ">";
        RI::ai(1);

        $this->mStarted = true;
    }

    public function flush() {
        if (!$this->mStarted)
            return;

        //$this->tryStart();

        RI::ai(-1);
        echo RI::ni(), "</ul>";

        $this->mStarted = false;
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeStyleSheet(__DIR__ . '\assets\html-map-renderer.css');
        //$Head->writeScript(__DIR__ . '\assets\html-map-renderer.js', true);
    }

    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @return bool true to stop or any other value to continue
     */
    function map($key, $value) {
        $this->tryStart();
        if(is_array($value))
            $value = new ArraySequence($value);

//        if($value instanceof IHasURL) {
//            echo RI::ni(), "<a href='" . $value->getURL() . "'>";
//            RI::ai(1);
//        }

        echo RI::ni(), "<li class='" . self::CSS_CLASS_MAP_KEY_PAIR . "'>";
        RI::ai(1);
        echo RI::ni(), "<div>";
        RI::ai(1);

        $key = ucwords(str_replace('_', ' ', $key));
        if(strlen($key) <= 3)
            $key = strtoupper($key);
        echo RI::ni(), "<label>", $key, "</label>";

        if ($value instanceof IRenderHTML) {
            $value->renderHTML($this->mRequest);

        } elseif ($value instanceof IKeyMap) {
            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapKeys($Renderer);
            $Renderer->flush();

        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapSequence($Renderer);
            $Renderer->flush();

        } elseif (is_bool($value)) {
            echo RI::ni(), "<span>", $value ? 'True' : 'False', "</span>";

        } else {
            echo RI::ni(), "<span>", htmlspecialchars(Describable::get($value)->getDescription()), "</span>";

        }
        RI::ai(-1);
        echo RI::ni(), "</div>";

        RI::ai(-1);
        echo RI::ni(), "</li>";

//        if($value instanceof IHasURL) {
//            RI::ai(-1);
//            echo RI::ni(), "</a>";
//        }
    }


    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        $this->tryStart();

        if(is_array($value))
            $value = new ArraySequence($value);

        //$path = $this->mRequest->getDomainPath();
//        if($value instanceof IHasURL) {
//            echo RI::ni(), "<a href='" . $path . $value->getURL() . "'>";
//            RI::ai(1);
//        }

        echo RI::ni(), "<li class='" . self::CSS_CLASS_SEQUENCE_ITEM . "'>";
        RI::ai(1);

        echo RI::ni(), "<h3>", Describable::get($value)->getTitle() . "</h3>";

        if ($value instanceof IRenderHTML) {
            $value->renderHTML($this->mRequest);

        } elseif ($value instanceof IKeyMap) {
            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapKeys($Renderer);
            $Renderer->flush();

        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapSequence($Renderer);
            $Renderer->flush();

        } elseif (is_bool($value)) {
            echo RI::ni(), "<span>", $value ? 'True' : 'False', "</span>";

        } else {
            echo RI::ni(), "<span>", htmlspecialchars(Describable::get($value)->getDescription()), "</span>";

        }
        RI::ai(-1);
        echo RI::ni(), "</li>";

//        if($value instanceof IHasURL) {
//            RI::ai(-1);
//            echo RI::ni(), "</a>";
//        }
    }
}

