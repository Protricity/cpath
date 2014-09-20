<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:13 PM
 */
namespace CPath\Render\XML;

use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IMappableSequence;
use CPath\Data\Map\ISequenceMap;
use CPath\Framework\Render\Util\RenderIndents as RI;

class XMLSequenceMapRenderer implements ISequenceMap
{
    const DELIMIT = ', ';
    private $mElementName;


    public function __construct($elementName = 'item') {
        $this->mElementName = $elementName;
    }


    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param mixed $value
     * @return bool false to continue, true to stop
     */
    function mapNext($value) {
        if ($value instanceof IMappableKeys) {
            $Renderer = new XMLKeyMapRenderer($this->mElementName, false);
            $value->mapKeys($Renderer);

        } elseif ($value instanceof IMappableSequence || is_array($value)) { // TODO: array of arrays?
            $Map = new XMLKeyMapRenderer($this->mElementName, false);
            $Map->map($this->mElementName, $value);

        } else {
            echo RI::ni(), "<", $this->mElementName, ">", htmlspecialchars($value), "</", $this->mElementName, ">";
        }
    }
}