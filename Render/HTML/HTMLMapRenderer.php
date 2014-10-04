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
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Describable\Describable;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\ClassAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

class HTMLMapRenderer implements IKeyMapper, ISequenceMapper, IHTMLSupportHeaders
{
    const CSS_CLASS = 'html-map-renderer';

    const CSS_CLASS_SEQUENCE_LIST = 'sequence-list';
    const CSS_CLASS_SEQUENCE_ITEM = 'sequence-item';

    const CSS_CLASS_KEY_MAP = 'key-map';
    const CSS_CLASS_KEY_MAP_PAIR = 'key-map-pair';
    const CSS_CLASS_KEY_MAP_TITLE = 'key-map-title';
    const CSS_CLASS_KEY_NAME = 'key-name';
    const CSS_CLASS_KEY_VALUE = 'key-value';

    private $mStarted = false;
    private $mAttr;
    private $mRequest;
    private $mKeyCount = 0;

    public function __construct(IRequest $Request, IAttributes $Attr = null) {
        $this->mRequest = $Request;
        $this->mAttr = $Attr;
    }

    function __destruct() {
        $this->flush();
    }

    private function tryStart($cls=null) {
        if ($this->mStarted)
            return;

        $Attr = new ClassAttributes(self::CSS_CLASS, $cls);
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

    private function mapValue($value) {
        if ($value instanceof IRenderHTML) {
            $value->renderHTML($this->mRequest);

            // Check ISequenceMap first
        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapSequence($Renderer);
            $Renderer->flush();

        } elseif ($value instanceof IKeyMap) {
            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapKeys($Renderer);
            $Renderer->flush();

        } elseif (is_bool($value)) {
            echo RI::ni(), "<span class='", self::CSS_CLASS_KEY_VALUE, "'>", $value ? 'True' : 'False', "</span>";

        } else {
            echo RI::ni(), "<span class='", self::CSS_CLASS_KEY_VALUE, "'>", htmlspecialchars(Describable::get($value)->getDescription()), "</span>";

        }
    }

    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @return bool true to stop or any other value to continue
     */
    function map($key, $value) {
        $this->tryStart(self::CSS_CLASS_KEY_MAP);
        if(is_array($value))
            $value = new ArraySequence($value);

        $css = array(self::CSS_CLASS_KEY_MAP_PAIR);
        if($this->mKeyCount === 0 && $key === IKeyMap::KEY_TITLE)
            $css[] = self::CSS_CLASS_KEY_MAP_TITLE;

        echo RI::ni(), "<li class='", implode(' ', $css), "'>";
        RI::ai(1);

        $key = ucwords(str_replace('_', ' ', $key));
        if(strlen($key) <= 3)
            $key = strtoupper($key);
        echo RI::ni(), "<label class='", self::CSS_CLASS_KEY_NAME, "'>", $key, "</label>";

        $this->mapValue($value);

        RI::ai(-1);
        echo RI::ni(), "</li>";

        return false;
    }


    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        $this->tryStart(self::CSS_CLASS_SEQUENCE_LIST);

        if(is_array($value))
            $value = new ArraySequence($value);

        //$path = $this->mRequest->getDomainPath();
//        if($value instanceof IHasURL) {
//            echo RI::ni(), "<a href='" . $path . $value->getURL() . "'>";
//            RI::ai(1);
//        }

        echo RI::ni(), "<li class='" . self::CSS_CLASS_SEQUENCE_ITEM . "'>";
        RI::ai(1);

        if ($value instanceof IRenderHTML) {
            $value->renderHTML($this->mRequest);

        } elseif ($value instanceof IKeyMap) {
            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapKeys($Renderer);
            $Renderer->flush();

        } elseif ($value instanceof ISequenceMap) {
            //echo RI::ni(), "<h3>", Describable::get($value)->getTitle() . "</h3>";

            $Renderer = new HTMLMapRenderer($this->mRequest);
            $value->mapSequence($Renderer);
            $Renderer->flush();

        } elseif (is_bool($value)) {
            //echo RI::ni(), "<h3>", Describable::get($value)->getTitle() . "</h3>";
            echo RI::ni(), "<span>", $value ? 'True' : 'False', "</span>";

        } else {
            //echo RI::ni(), "<h3>", Describable::get($value)->getTitle() . "</h3>";
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

