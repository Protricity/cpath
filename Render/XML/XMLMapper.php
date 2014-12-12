<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:13 PM
 */
namespace CPath\Render\XML;

use CPath\Data\Map\ArrayKeyMap;
use CPath\Data\Map\ArraySequence;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Request\IRequest;

class XMLMapper implements IKeyMapper, ISequenceMapper
{
    private $mIsArray = false;
    private $mRootElement, $mDeclaration;
	private $mRequest;
	private $mCount = 0;
	private $mItemName = 'item';

    public function __construct(IRequest $Request, $rootElementName='root', $declaration=false) {
        $this->mRootElement = $rootElementName;
        $this->mDeclaration = $declaration;
	    $this->mRequest = $Request;
    }

	public function setItemName($itemName) {
		$this->mItemName = $itemName;
	}

    function __destruct() {
	    if($this->mIsArray === false) {
		    RI::ai(-1);
		    echo RI::ni(), "</", $this->mRootElement, ">";
	    }
    }

	function __clone() {
		$this->mIsArray = null;
		$this->mCount = 0;
	}


    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @return bool false to continue, true to stop
     */
    function map($key, $value) {
	    $this->mCount++;
	    if($this->mIsArray === null) {
		    if($this->mDeclaration === true)
			    echo "<?xml version='1.0' encoding='UTF-8'?>", RI::ni();
		    elseif(is_string($this->mDeclaration))
			    echo $this->mDeclaration, RI::ni();

		    echo "<", $this->mRootElement, ">";
		    RI::ai(1);

		    $this->mIsArray = false;
	    }

	    $this->mIsArray = true;

	    if(is_array($value)) {
		    reset($value);
		    if(is_string(key($value)))
			    $value = new ArrayKeyMap($value);
		    else
			    $value = new ArraySequence($value);
	    }

        if ($value instanceof ISequenceMap) {
	        $Mapper = clone $this;
	        $Mapper->mDeclaration = false;
	        $Mapper->mRootElement = $key;
	        $value->mapSequence($Mapper);

        } elseif ($value instanceof IKeyMap) {
	        $Mapper = clone $this;
	        $Mapper->mDeclaration = false;
	        $Mapper->mRootElement = $key;
	        $value->mapKeys($Mapper);

        } else {
            echo RI::ni(), "<", $key, ">", htmlspecialchars($value), "</", $key, ">";

        }
    }


	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool false to continue, true to stop
	 */
	function mapNext($value, $_arg = null) {
		$this->mCount++;
		if($this->mIsArray === null)
			$this->mIsArray = true;

		if (is_array($value))
			$value = new ArraySequence($value);

		if ($value instanceof IKeyMap) {
			$Renderer = clone $this;
			$Renderer->mDeclaration = false;
			$Renderer->mRootElement = $this->mItemName;
			$value->mapKeys($Renderer);

		} elseif ($value instanceof ISequenceMap) { // TODO: array of arrays?
			$Renderer = clone $this;
			$Renderer->mDeclaration = false;
			$Renderer->mRootElement = $this->mItemName;
			$value->mapSequence($Renderer);

		} else {
			echo RI::ni(), "<", $this->mItemName, ">", htmlspecialchars($value), "</", $this->mItemName, ">";
		}
	}
}