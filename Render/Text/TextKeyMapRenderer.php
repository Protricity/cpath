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

class TextKeyMapRenderer implements IKeyMapper
{
    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @return bool false to continue, true to stop
     */
    function map($key, $value) {
        if(is_array($value))
            $value = new ArraySequence($value);

        if($value instanceof IKeyMap) {
            echo RI::ni(), $key, ": ";
            RI::i(1);
            $value->mapKeys($this);
            RI::i(-1);

        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new TextSequenceMapRenderer();
            echo RI::ni(), $key, ": ";
            RI::i(1);
            $value->mapSequence($Renderer);
            RI::i(-1);

        } else {
            echo RI::ni(), "{$key}: {$value}";
        }
    }
}

