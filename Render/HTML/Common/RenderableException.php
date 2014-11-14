<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 5:33 PM
 */
namespace CPath\Render\HTML\Common;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class RenderableException extends \Exception implements IRenderHTML
{
	private $mRenderable;

	public function __construct(IRenderHTML $Renderable, $message=null, $code=null, \Exception $Previous=null) {
		$this->mRenderable = $Renderable;
		parent::__construct($message, $message, $code, $Previous);
	}


	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$this->mRenderable->renderHTML($Request, $Attr);
	}
}