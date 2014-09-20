<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:32 PM
 */
namespace CPath\Render\Text;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IMappableSequence;
use CPath\Data\Map\ISequenceMap;
use CPath\Framework\Render\Util\RenderIndents as RI;

class TextSequenceMapRenderer implements ISequenceMap
{

    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param mixed $value
     * @return bool false to continue, true to stop
     */
    function mapNext($value) {
        if(is_array($value))
            $value = new ArraySequence($value);

        if ($value instanceof IMappableKeys) {
            $Map = new TextKeyMapRenderer();
            $value->mapKeys($Map);

        } elseif ($value instanceof IMappableSequence) {
            $Renderer = new TextSequenceMapRenderer();
            $value->mapSequence($Renderer);

        } else {
            echo RI::ni(), $value;
        }
    }
}