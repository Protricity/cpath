<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/14
 * Time: 8:14 PM
 */
namespace CPath\Response\Common;

use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Response\Response;

class SequenceResponse extends Response implements ISequenceMap, \Countable
{
	private $mCollection = array();

	function __construct($message = null, $status = true) {
		parent::__construct($message, $status);
	}

	public function add($item) {
		$this->mCollection[] = $item;
	}

	public function getCollection() {
		return $this->mCollection;
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 */
	function mapSequence(ISequenceMapper $Map) {
		foreach ($this->mCollection as $item)
			$Map->mapNext($item);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Count elements of an object
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 */
	public function count() {
		return count($this->mCollection);
	}
}

