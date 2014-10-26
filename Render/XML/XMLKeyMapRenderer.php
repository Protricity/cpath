<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:13 PM
 */
namespace CPath\Render\XML;

use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;

class XMLKeyMapRenderer implements IKeyMapper
{
    private $mStarted = false;
    private $mRootElement, $mDeclaration;
	private $mRequest;

    public function __construct(IRequest $Request, $rootElementName='root', $declaration=false) {
        $this->mRootElement = $rootElementName;
        $this->mDeclaration = $declaration;
	    $this->mRequest = $Request;
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

        if ($value instanceof ISequenceMap) {
            $Renderer = new XMLSequenceMapRenderer($this->mRequest, $key);
            $value->mapSequence($Renderer);

        } elseif ($value instanceof IKeyMap) {
	        $Renderer = new XMLKeyMapRenderer($this->mRequest, $key);
	        $value->mapKeys($Renderer);

        } else {
            echo RI::ni(), "<", $key, ">", htmlspecialchars($value), "</", $key, ">";

        }
    }
}