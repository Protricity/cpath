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
use CPath\Data\Map\ISequenceMapper;
use CPath\Data\Value\IHasURL;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Request\IRequest;


class HTMLMapper implements IKeyMapper, ISequenceMapper
{
	private $mIsArray = false;
	private $mRequest;
	private $mCount = 0;

	public function __construct(IRequest $Request) {
		$this->mRequest = $Request;
	}

	function __destruct() {
		if($this->mIsArray === false) {
			RI::ai(-1);
			echo RI::ni(), "</dl>";

		} else if($this->mIsArray === true) {
			RI::ai(-1);
			echo RI::ni(), "</ul>";
		}
	}

	function __clone() {
		$this->mIsArray = null;
		$this->mCount = 0;
	}


	/**
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool true to stop or any other value to continue
	 */
	function map($key, $value) {
		$this->mCount++;

		if($this->mIsArray === null) {
			echo RI::ni(), "<dl>";
			RI::ai(1);
			$this->mIsArray = false;
		}

		if (is_array($value))
			$value = new ArraySequence($value);

		echo RI::ni(), "<dt>", $key, "</dt>";
		echo RI::ni(), "<dd>";

		if($value instanceof IHasURL) {
			RI::ai(1);
			echo RI::ni(), "<a href=", $value->getURL($this->mRequest), ">";
		}

		if ($value instanceof IRenderHTML) {
			RI::ai(1);

			$value->renderHTML($this->mRequest);

			RI::ai(-1);
			echo RI::ni();

		} elseif ($value instanceof IKeyMap) {
			RI::ai(1);

			$Mapper = clone $this;
			$value->mapKeys($Mapper);
			unset($Renderer);

			RI::ai(-1);
			echo RI::ni();

		} elseif ($value instanceof ISequenceMap) {
			RI::ai(1);

			$Mapper = clone $this;
			$value->mapSequence($Mapper);
			unset($Renderer);

			RI::ai(-1);
			echo RI::ni();

		} elseif (is_string($value)) {
			echo $value ? nl2br(htmlspecialchars($value)) : '&nbsp;';

		} else {
			echo var_export($value, true);
			//echo RI::ni(), $value ? htmlspecialchars(new Description($value)) : '&nbsp;';

		}

		if($value instanceof IHasURL) {
			echo RI::ni(), "</a>";
			RI::ai(-1);
		}

		echo "</dd>";

		return false;
	}

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool false to continue, true to stop
	 */
	function mapNext($value, $_arg = null) {
		$this->mCount++;
		if($this->mIsArray === null) {
			echo RI::ni(), "<ul>";
			RI::ai(1);
			$this->mIsArray = true;
		}

		if(is_array($value))
			$value = new ArraySequence($value);

		echo RI::ni(), "<li>";
		RI::ai(1);

		$Attr = null; // new ClassAttributes(self::CSS_CLASS_KEY_CONTENT);
		if ($value instanceof IRenderHTML) {
			$value->renderHTML($this->mRequest, $Attr);

		} elseif ($value instanceof IKeyMap) {
			$Renderer = new HTMLMapper($this->mRequest, $Attr);
			$value->mapKeys($Renderer);
			unset($Renderer);

		} elseif ($value instanceof ISequenceMap) {
			$Renderer = new HTMLSequenceMapRenderer($this->mRequest, $Attr);
			$value->mapSequence($Renderer);
			$Renderer->flush();

		} elseif (is_bool($value)) {
			echo RI::ni(), $value ? 'True' : 'False';

		} else {
			echo RI::ni(), htmlspecialchars($value);

		}


		RI::ai(-1);
		echo RI::ni(), "</li>";
	}
}