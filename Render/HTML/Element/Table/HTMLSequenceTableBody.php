<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/31/2015
 * Time: 3:53 PM
 */
namespace CPath\Render\HTML\Element\Table;

use CPath\Data\Map\ArrayKeyMap;
use CPath\Data\Map\CallbackKeyMapper;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\HTMLConfig;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\Text\IRenderText;
use CPath\Request\IRequest;

class HTMLSequenceTableBody extends HTMLElement implements ISequenceMapper, IKeyMapper
{
	const CLS_TBODY_SEQUENCE = 'tbody-sequence';
	private $mMap;
	private $mRowCount = 0;
	private $mRequest;
	/**
	 * @param ISequenceMap $Map
	 * @param null $classList
	 */
	public function __construct(ISequenceMap $Map, $classList = null) {
		parent::__construct('tbody');
		$this->addClass(self::CLS_TBODY_SEQUENCE);
		$this->mMap = $Map;

		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=2; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeScript(__DIR__ . '/assets/tbody.js');
		$Head->writeStyleSheet(__DIR__ . '/assets/tbody.css');
		parent::writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $ContentAttr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		$this->mRequest = $Request;
		$this->mMap->mapSequence($this);
		$this->mRequest = null;
		parent::renderContent($Request, $ContentAttr, $Parent);
	}

	/**
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool true to stop or any other value to continue
	 */
	function map($key, $value, $_arg=null) {
		echo RI::ni(), "<td>";
		if($this->mRequest && $value instanceof IRenderHTML) {
			$value->renderHTML($this->mRequest, null, $this);

		} else if($this->mRequest && $value instanceof IRenderText) {
			$value->renderText($this->mRequest);

		} else {
			HTMLConfig::renderNamedValue($key, $value, $_arg);
		}
		echo "</td>";
	}

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool true to stop or any other value to continue
	 */
	function mapNext($value, $_arg = null) {
		echo RI::ni(), "<tr>";
		echo RI::ai(1);
		if(is_array($value))
			$value = new ArrayKeyMap($value);

		if ($value instanceof IKeyMap) {
			if($this->mRowCount === 0) {
				$value->mapKeys(
					new CallbackKeyMapper(
						function($key) {
							echo RI::ni(), "<th>", ucwords(preg_replace('/[_-]/', ' ', $key)), "</th>";
						}
					)
				);
				echo RI::ai(-1);
				echo RI::ni(), "</tr>";
				echo RI::ni(), "<tr>";
				echo RI::ai(1);
			}
			$value->mapKeys($this);

		} else {
			echo RI::ni(), "<td>", HTMLConfig::renderValue($value), "</td>";
		}
		echo RI::ai(-1);
		echo RI::ni(), "</tr>";
		$this->mRowCount++;
	}
}