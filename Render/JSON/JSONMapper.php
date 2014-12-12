<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 1:27 PM
 */
namespace CPath\Render\JSON;

use CPath\Data\Map\ArrayKeyMap;
use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Request\IRequest;

class JSONMapper implements IKeyMapper, ISequenceMapper
{
	const DELIMIT = ', ';
	private $mNextDelim = null;

	private $mIsArray = false;
	private $mRequest;
	private $mCount = 0;

	function __construct(IRequest $Request) {
		$this->mRequest = $Request;
	}

	function __destruct() {
		if($this->mIsArray === false) {
			echo '}';

		} else if($this->mIsArray === true) {
			echo ']';

		} else {
			echo '{}';
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
	 * @return bool false to continue, true to stop
	 */
	function map($key, $value) {
		if($this->mIsArray === null) {
			echo '{';
			$this->mIsArray = false;
		}

		if ($this->mNextDelim)
			echo $this->mNextDelim;

		echo json_encode($key), ':';

		if (is_array($value)) {
			reset($value);
			if (is_string(key($value)))
				$value = new ArrayKeyMap($value);
			else
				$value = new ArraySequence($value);
		}

		if ($value instanceof ISequenceMap) {
			$this->mNextDelim = null;
			$Mapper = clone $this;
			$value->mapSequence($Mapper);

		} elseif ($value instanceof IKeyMap) {
			$this->mNextDelim = null;
			$Mapper = clone $this;
			$value->mapKeys($Mapper);

		} else {
			echo json_encode($value);

		}

		$this->mNextDelim = self::DELIMIT;
	}


	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool false to continue, true to stop
	 */
	function mapNext($value, $_arg = null) {
		if($this->mIsArray === null) {
			echo '[';
			$this->mIsArray = true;
		}

		if ($this->mCount)
			echo self::DELIMIT;

		if(is_array($value))
			$value = new ArraySequence($value);

		if ($value instanceof IKeyMap) {
			$Renderer = clone $this;
			$value->mapKeys($Renderer);

		} elseif ($value instanceof ISequenceMap) {
			$Renderer = clone $this;
			$value->mapSequence($Renderer);

		} else {
			echo json_encode($value);
		}

		$this->mCount++;
	}
}