<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:03 PM
 */
namespace CPath\Render\JSON;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Request\IRequest;

class JSONSequenceMapRenderer implements ISequenceMapper
{
    const DELIMIT = ', ';
    private $mStarted = false;
    private $mCount = 0;

	private $mRequest;
	function __construct(IRequest $Request) {
		$this->mRequest = $Request;
	}

    function __destruct() {
        $this->flush();
    }

    private function tryStart() {
        if (!$this->mStarted) {
            $this->mStarted = true;
            echo '[';
        }
    }

    public function flush() {
        if (!$this->mStarted) {
            echo '[]';
            $this->mStarted = false;
            return;
        }

        echo ']';
        $this->mStarted = false;
    }

    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        $this->tryStart(true);
        if ($this->mCount)
            echo self::DELIMIT;

        if(is_array($value))
            $value = new ArraySequence($value);

        if ($value instanceof IKeyMap) {
            $Renderer = new JSONKeyMapRenderer($this->mRequest);
            $value->mapKeys($Renderer);

        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new JSONSequenceMapRenderer($this->mRequest);
            $value->mapSequence($Renderer);

        } else {
            echo json_encode($value);
        }

        $this->mCount++;
    }
}