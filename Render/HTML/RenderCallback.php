<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 3:20 PM
 */
namespace CPath\Render\HTML;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\IMappableSequence;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class RenderCallback implements IMappableSequence, IRenderHTML
{
    private $mClosure;

    public function __construct(\Closure $Closure) {
        $this->mClosure = $Closure;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $call = $this->mClosure;
        $call($Request, $Attr);
    }

    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        $args = func_get_args();
        return call_user_func_array($this->mClosure, $args);
    }
}

