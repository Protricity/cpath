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
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Describable\Describable;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\ClassAttr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLSequenceMapRenderer implements ISequenceMapper, IHTMLSupportHeaders
{
	const CSS_CLASS = 'html-sequence-map-renderer';

	const CSS_CLASS_SEQUENCE_ITEM = 'sequence-item';

	const CSS_CLASS_FIRST = 'first';

	private $mStarted = false;
	private $mAttr;
	private $mRequest;
	private $mCount = 0;

	public function __construct(IRequest $Request, IAttributes $Attr = null) {
		$this->mRequest = $Request;
		$this->mAttr = $Attr;
	}

	function __destruct() {
		$this->flush();
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeStyleSheet(__DIR__ . '\assets\html-map-renderer.css');
		//$Head->writeScript(__DIR__ . '\assets\html-sequence-map-renderer.js', true);
	}

	private function tryStart($cls=null) {
		if ($this->mStarted)
			return;

		$Attr = new ClassAttr(self::CSS_CLASS, $cls);

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
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool false to continue, true to stop
	 */
	function mapNext($value, $_arg = null) {
		$this->mCount++;
		$this->tryStart();

		if(is_array($value))
			$value = new ArraySequence($value);

		echo RI::ni(), "<li class='" . self::CSS_CLASS_SEQUENCE_ITEM . "'>";
		RI::ai(1);

		$Attr = null; // new ClassAttributes(self::CSS_CLASS_KEY_CONTENT);
		if ($value instanceof IRenderHTML) {
			$value->renderHTML($this->mRequest, $Attr);

		} elseif ($value instanceof IKeyMap) {
			$Renderer = new HTMLKeyMapRenderer($this->mRequest, $Attr);
			$value->mapKeys($Renderer);
			$Renderer->flush();

		} elseif ($value instanceof ISequenceMap) {
			$Renderer = new HTMLSequenceMapRenderer($this->mRequest, $Attr);
			$value->mapSequence($Renderer);
			$Renderer->flush();

		} elseif (is_bool($value)) {
			echo RI::ni(), $value ? 'True' : 'False';

		} else {
			echo RI::ni(), htmlspecialchars(Describable::get($value)->getDescription());

		}


		RI::ai(-1);
		echo RI::ni(), "</li>";
	}
}

