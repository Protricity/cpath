<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 3:20 PM
 */
namespace CPath\Data\Map;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class RenderSequence implements ISequenceMap, IRenderHTML
{
    private $mMappable;
    private $mClosure;
    private $mRequest = null;

    public function __construct(IMappableSequence $Mappable, \Closure $Closure) {
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
        $this->mMappable->mapSequence($this);
        $this->mRequest = null;

    }

    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IMappableKeys|IMappableSequence $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        $args = func_get_args();
        array_unshift($args, $this->mRequest);
        call_user_func_array($this->mClosure, $args);
    }
}

