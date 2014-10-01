<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 9:12 AM
 */
namespace CPath\Render\JSON;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;

class JSONKeyMapRenderer implements IMappableKeys {
    const DELIMIT = ', ';
    private $mStarted = false, $mNextDelim=null;


    function __destruct() {
        $this->flush();
    }

    private function tryStart() {
        if(!$this->mStarted) {
            $this->mStarted = true;
            echo '{';
        }
    }

    public function flush() {
        if(!$this->mStarted) {
            echo '{}';
            $this->mStarted = false;
            return;
        }

        echo '}';
        $this->mStarted = false;
    }

    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @return bool false to continue, true to stop
     */
    function map($key, $value) {
        $this->tryStart(false);

        if($this->mNextDelim)
            echo $this->mNextDelim;

        echo json_encode($key), ':';

        if(is_array($value))
            $value = new ArraySequence($value);

        if($value instanceof IKeyMap) {
            $this->mNextDelim = null;
            $Renderer = new JSONKeyMapRenderer();
            $value->mapKeys($Renderer);

        } elseif ($value instanceof ISequenceMap) {
            $this->mNextDelim = null;
            $Renderer = new JSONSequenceMapRenderer();
            $value->mapSequence($Renderer);

        } else {
            echo json_encode($value);

        }

        $this->mNextDelim = self::DELIMIT;
    }
}
