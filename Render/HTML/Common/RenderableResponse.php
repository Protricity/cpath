<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/9/14
 * Time: 4:25 PM
 */
namespace CPath\Render\HTML\Common;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\Response;

final class RenderableResponse extends Response implements IRenderHTML
{
	private $mRenderable;

	public function __construct(IRenderHTML $Renderable, $message = null, $status = true) {
		$this->mRenderable = $Renderable;
		parent::__construct($message, $status);
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