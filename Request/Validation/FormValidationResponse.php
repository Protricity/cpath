<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/11/14
 * Time: 1:36 PM
 */
namespace CPath\Request\Validation;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\IResponse;
use CPath\Response\Response;

class FormValidationResponse extends Response implements IRenderHTML, IHTMLSupportHeaders
{
	/** @var FormValidation */
	private $mForm;

	public function __construct(FormValidation $Form, $message=null, $code=IResponse::HTTP_SUCCESS) {
		parent::__construct($message, $code);
		$this->mForm = $Form;
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$this->mForm->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param \CPath\Render\HTML\IRenderHTML|\CPath\Request\Validation\IHTMLContainer $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$this->mForm->renderHTML($Request, $Attr, $Parent);
	}
}