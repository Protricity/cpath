<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/18/14
 * Time: 12:57 PM
 */
namespace CPath\Request\Parameter\Exceptions;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLForm;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Parameter\IRequestParameter;

class ParameterException extends RequestException implements IRenderHTML
{
	private $mParameter;

	public function __construct(IRequestParameter $Parameter, $message = null) {
		$this->mParameter = $Parameter;
		parent::__construct($message ? : "Parameter failed validation: " . $Parameter->getFieldName());
	}

	public function getForm() {
		$Form = new HTMLForm('POST', 'register', 'register',
			$this->mParameter
		);
		return $Form;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Form = new HTMLForm('POST', 'register', 'register'
			//new HTMLFormAjaxSupport("Registration"),
		);
		$Form->addContent($this->mParameter);
	}
}