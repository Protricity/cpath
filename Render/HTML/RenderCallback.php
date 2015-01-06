<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 3:20 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

class RenderCallback implements IRenderHTML
{
    private $mClosure;

    public function __construct(\Closure $Closure) {
        $this->mClosure = $Closure;
    }

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param Attribute\IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
        $call = $this->mClosure;
        $call($Request, $Attr);
    }

}

