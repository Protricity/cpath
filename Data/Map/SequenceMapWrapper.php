<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 12:46 PM
 */
namespace CPath\Data\Map;

abstract class SequenceMapWrapper implements ISequenceMapper
{
	private $mCount = 0;
	private $mMapper;

	public function __construct(ISequenceMapper $Mapper) {
		$this->mMapper   = $Mapper;
	}

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 */
	abstract protected function processMappedArgs(&$value, &$_arg = null);

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool false to continue, true to stop
	 */
	function mapNext($value, $_arg = null) {
		$this->mCount++;
		if ($_arg === null && func_num_args() <= 2) {
			$this->processMappedArgs($value);
			return $this->mMapper->mapNext($value);

		} else {
			$args = func_get_args();
			call_user_func_array(array($this, 'processMappedArgs'), $args);
			return call_user_func_array(array($this->mMapper, 'mapNext'), $args);

		}
	}

	function getCount() {
		return $this->mCount;
	}
}

