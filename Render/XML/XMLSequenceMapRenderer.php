<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 2:13 PM
 */
namespace CPath\Render\XML;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;

class XMLSequenceMapRenderer implements ISequenceMapper
{
    const DELIMIT = ', ';
    private $mElementName;

	private $mRequest;

    public function __construct(IRequest $Request, $elementName = 'item') {
        $this->mElementName = $elementName;
	    $this->mRequest = $Request;
    }


    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        if ($value instanceof IKeyMap) {
            $Renderer = new XMLKeyMapRenderer($this->mRequest, $this->mElementName, false);
            $value->mapKeys($this->mRequest, $Renderer);

        } elseif ($value instanceof ISequenceMap || is_array($value)) { // TODO: array of arrays?
            $Map = new XMLKeyMapRenderer($this->mRequest, $this->mElementName, false);
            $Map->map($this->mElementName, $value);

        } else {
            echo RI::ni(), "<", $this->mElementName, ">", htmlspecialchars($value), "</", $this->mElementName, ">";
        }
    }
}