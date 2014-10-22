<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 1:06 PM
 */
namespace CPath\Data\Map;

class CallbackSequenceMapper extends SequenceMapWrapper
{
	private $mMapper;
	private $mCallback;

	public function __construct(ISequenceMapper $Mapper, \Closure $Callback) {
		$this->mMapper   = $Mapper;
		$this->mCallback = $Callback;
	}

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 */
	protected function processMappedArgs(&$value, &$_arg = null) {
		$args = func_get_args();
		call_user_func_array($this->mCallback, $args);
	}
}