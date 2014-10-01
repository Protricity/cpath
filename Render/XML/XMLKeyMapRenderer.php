<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:13 PM
 */
namespace CPath\Render\XML;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Framework\Render\Util\RenderIndents as RI;

class XMLKeyMapRenderer implements IMappableKeys
{
    private $mStarted = false;
    private $mRootElement, $mDeclaration;

    public function __construct($rootElementName='root', $declaration=false) {
        $this->mRootElement = $rootElementName;
        $this->mDeclaration = $declaration;
    }

    function __destruct() {
        $this->flush();
    }

    private function tryStart() {
        if($this->mStarted)
            return;
        //throw new \InvalidArgumentException(__CLASS__ . " was already started");

        if($this->mDeclaration === true)
            echo "<?xml version='1.0' encoding='UTF-8'?>", RI::ni();
        elseif(is_string($this->mDeclaration))
            echo $this->mDeclaration, RI::ni();

        echo "<", $this->mRootElement, ">";
        RI::ai(1);

        $this->mStarted = true;
    }

    public function flush() {
        $this->tryStart();

        RI::ai(-1);
        echo RI::ni(), "</", $this->mRootElement, ">";

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

        if(is_array($value))
            $value = new ArraySequence($value);

        if ($value instanceof IKeyMap) {
            $Renderer = new XMLKeyMapRenderer($key);
            $value->mapKeys($Renderer);

        } elseif ($value instanceof ISequenceMap) {
            $Renderer = new XMLSequenceMapRenderer($key);
            $value->mapSequence($Renderer);

        } else {
            echo RI::ni(), "<", $key, ">", htmlspecialchars($value), "</", $key, ">";

        }
    }
}