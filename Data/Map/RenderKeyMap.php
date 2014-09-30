<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 3:25 PM
 */
namespace CPath\Data\Map;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class RenderKeyMap implements IKeyMap, IRenderHTML
{
    private $mMappable;
    private $mClosure;
    private $mRequest = null;

    public function __construct(IMappableKeys $Mappable, \Closure $Closure) {
        $this->mMappable = $Mappable;
        $this->mClosure = $Closure;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $this->mRequest = $Request;
        $this->mMappable->mapKeys($this);
        $this->mRequest = null;
    }

    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IMappableKeys|IMappableSequence $value
     * @return bool true to stop or any other value to continue
     */
    function map($key, $value) {
        $Closure = $this->mClosure;
        $Closure($this->mRequest, $key, $value);
    }
}