<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 1:33 PM
 */
namespace CPath\Render\Text;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Request\IRequest;

class TextMapper implements IKeyMapper, ISequenceMapper
{
	private $mIsArray = false;
	private $mRequest;
	private $mCount = 0;
	private static $mStarted = false;

	function __construct(IRequest $Request) {
		$this->mRequest = $Request;
	}

	function __clone() {
		$this->mIsArray = null;
		$this->mCount = 0;
	}

	/**
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool false to continue, true to stop
	 */
	function map($key, $value) {
		$this->mCount++;

		if($this->mIsArray === null) {
			$this->mIsArray = false;
		}

		if (is_array($value))
			$value = new ArraySequence($value);

		if (self::$mStarted)
			echo RI::ni();
		self::$mStarted = true;

		if ($value instanceof ISequenceMap) {
			$Mapper = clone $this;
			echo $key, ": ";
			RI::i(1);
			$value->mapSequence($Mapper);
			RI::i(-1);

		} elseif ($value instanceof IKeyMap) {
			$Mapper = clone $this;
			echo $key, ": ";
			RI::i(1);
			$value->mapKeys($Mapper);
			RI::i(-1);

		} elseif (is_string($value)) {
			echo "{$key}: {$value}";
		}
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
			$this->mIsArray = true;
		}

		if(is_array($value))
			$value = new ArraySequence($value);

		if ($value instanceof IKeyMap) {
			$Mapper = clone $this;
			$value->mapKeys($Mapper);

		} elseif ($value instanceof ISequenceMap) {
			$Mapper = clone $this;
			$value->mapSequence($Mapper);

		} elseif (is_string($value)) {
			echo RI::ni(), $value;
		}
	}
}