<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 11:13 PM
 */
namespace CPath\Render\HTML;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Describable\Describable;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\ClassAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLKeyMapRenderer implements IKeyMapper, IHTMLSupportHeaders
{
	const CSS_CLASS = 'html-key-map-renderer';

	const CSS_CLASS_HEADER = 'key-header';

	const CSS_CLASS_KEY_MAP_PAIR = 'key-map-pair';
	const CSS_CLASS_KEY_NAME     = 'key-name';
	const CSS_CLASS_KEY_VALUE    = 'key-value';
	const CSS_CLASS_KEY_CONTENT  = 'key-content';

	private $mStarted = false;
	private $mAttr;
	private $mRequest;
	private $mKeyCount = 0;

	public function __construct(IRequest $Request, IAttributes $Attr = null) {
		$this->mRequest = $Request;
		$this->mAttr    = $Attr;
	}

	function __destruct() {
		$this->flush();
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeStyleSheet(__DIR__ . '\assets\html-map-renderer.css');
		//$Head->writeScript(__DIR__ . '\assets\html-map-renderer.js', true);
	}

	private function tryStart($cls = null) {
		if ($this->mStarted)
			return;

		$Attr = new ClassAttributes(self::CSS_CLASS, $cls);

		echo RI::ni(), "<ul", $Attr, $this->mAttr, ">";
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
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool true to stop or any other value to continue
	 */
	function map($key, $value) {
		$this->mKeyCount++;
		$this->tryStart();
		if (is_array($value))
			$value = new ArraySequence($value);

		$css = array(self::CSS_CLASS_KEY_MAP_PAIR);
//        if($this->mKeyCount === 1 && is_string($value))
//            $css[] = self::CSS_CLASS_HEADER;

		echo RI::ni(), "<li class='", implode(' ', $css), "'>";
		RI::ai(1);

		$key = ucwords(str_replace('_', ' ', $key));
		if (strlen($key) <= 2)
			$key = strtoupper($key);

		echo RI::ni(), "<div class='", self::CSS_CLASS_KEY_NAME, "'>", $key, "</div>";

		$Attr = new ClassAttributes(self::CSS_CLASS_KEY_CONTENT);
		if ($value instanceof IRenderHTML) {
			$value->renderHTML($this->mRequest, $Attr);

		} elseif ($value instanceof ISequenceMap) {
				$Renderer = new HTMLSequenceMapRenderer($this->mRequest, $Attr);
				$value->mapSequence($Renderer);
				$Renderer->flush();

		} elseif ($value instanceof IKeyMap) {
			$Renderer = new HTMLKeyMapRenderer($this->mRequest, $Attr);
			$value->mapKeys($Renderer);
			$Renderer->flush();

		} elseif (is_bool($value)) {
			echo RI::ni(), "<div class='", self::CSS_CLASS_KEY_VALUE, "'>", $value ? 'True' : 'False', "</div>";

		} else {
			echo RI::ni(), "<div class='", self::CSS_CLASS_KEY_VALUE, "'>", htmlspecialchars(Describable::get($value)->getDescription()), "</div>";

		}

		RI::ai(-1);
		echo RI::ni(), "</li>";

		return false;
	}

}