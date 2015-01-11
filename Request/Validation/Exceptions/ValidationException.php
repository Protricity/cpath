<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:32 AM
 */
namespace CPath\Request\Validation\Exceptions;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\Form\HTMLForm;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\ResponseRenderer;
use Exception;

class ValidationException extends RequestException implements IRenderHTML, IHTMLSupportHeaders
{
	private $mForm;
	private $mExceptions;

	/**
	 * @param HTMLForm $Form
	 * @param String|Array|Exception|Exception[] $Exceptions
	 * @internal param string $message
	 */
	public function __construct(HTMLForm $Form, $Exceptions = null) {
		$this->mForm = $Form;

		if (!is_array($Exceptions))
			$Exceptions = array($Exceptions);

		$message = sizeof($Exceptions) . " Exception(s) occurred during validation: ";

		foreach($Exceptions as $Ex)
			$message .= "\n\t" . ($Ex instanceof Exception ? $Ex->getMessage() : $Ex);

		$this->mExceptions = $Exceptions;

		parent::__construct($message, IResponse::HTTP_FORBIDDEN, $Exceptions[0] instanceof Exception ? $Exceptions[0] : new Exception($Exceptions[0]));
	}

	function getForm() {
		return $this->mForm;
	}

	function setForm(HTMLForm $Form) {
		$this->mForm = $Form;
		return $this;
	}

	function getExceptions() {
		return $this->mExceptions;
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
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$ResponseRenderer = new ResponseRenderer(new ExceptionResponse($this));
		$ResponseRenderer->renderHTML($Request, $Attr, $Parent);
		$this->mForm->renderHTML($Request, $Attr = null, $Parent);
	}
}