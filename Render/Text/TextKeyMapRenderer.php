<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\Text;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;

class TextKeyMapRenderer implements IKeyMapper
{
	private $mRequest;
	private static $mStarted = false;
	function __construct(IRequest $Request) {
		$this->mRequest = $Request;
	}

    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @return bool false to continue, true to stop
     */
    function map($key, $value) {
        if(is_array($value))
            $value = new ArraySequence($value);

	    if(self::$mStarted)
		    echo RI::ni();
	    self::$mStarted = true;

        if ($value instanceof ISequenceMap) {
            $Renderer = new TextSequenceMapRenderer($this->mRequest);
            echo $key, ": ";
            RI::i(1);
            $value->mapSequence($Renderer);
            RI::i(-1);

        } elseif($value instanceof IKeyMap) {
	        echo $key, ": ";
	        RI::i(1);
	        $value->mapKeys($this);
	        RI::i(-1);

        } elseif (is_string($value)) {
            echo "{$key}: {$value}";
        }
    }
}

