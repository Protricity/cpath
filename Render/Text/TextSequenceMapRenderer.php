<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:32 PM
 */
namespace CPath\Render\Text;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;

class TextSequenceMapRenderer implements ISequenceMapper
{
	private $mRequest;
	function __construct(IRequest $Request) {
		$this->mRequest = $Request;
	}

    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        if(is_array($value))
            $value = new ArraySequence($value);

        if ($value instanceof IKeyMap) {
            $Map = new TextKeyMapRenderer($this->mRequest);
            $value->mapKeys($Map);

        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new TextSequenceMapRenderer($this->mRequest);
            $value->mapSequence($Renderer);

        } elseif (is_string($value)) {
            echo RI::ni(), $value;
        }
    }
}