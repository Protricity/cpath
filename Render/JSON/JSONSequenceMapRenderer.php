<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:03 PM
 */
namespace CPath\Render\JSON;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IMappableSequence;
use CPath\Data\Map\ISequenceMap;

class JSONSequenceMapRenderer implements ISequenceMap
{
    const DELIMIT = ', ';
    private $mStarted = false;
    private $mCount = 0;


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
     * @param mixed $value
     * @return bool false to continue, true to stop
     */
    function mapNext($value) {
        $this->tryStart(true);
        if ($this->mCount)
            echo self::DELIMIT;

        if(is_array($value))
            $value = new ArraySequence($value);

        if ($value instanceof IMappableKeys) {
            $Renderer = new JSONKeyMapRenderer();
            $value->mapKeys($Renderer);

        } elseif ($value instanceof IMappableSequence) {
            $Renderer = new JSONSequenceMapRenderer();
            $value->mapSequence($Renderer);

        } else {
            echo json_encode($value);
        }

        $this->mCount++;
    }
}