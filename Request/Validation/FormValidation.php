<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 4:16 PM
 */
namespace CPath\Request\Validation;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Element\HTMLForm;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Log\StaticLogger;
use CPath\Request\Parameter\IRequestParameter;
use CPath\Request\Parameter\Parameter;
use CPath\Request\Parameter\RequiredParameter;
use CPath\Request\Exceptions\RequestException;
use CPath\Response\IResponse;

class FormValidation extends \Exception implements IResponse, IRenderHTML, IHTMLSupportHeaders {
    private $mContent = array();
    public function __construct($_Content=null) {
	    parent::__construct("Form Ready", IResponse::HTTP_SUCCESS);
	    if($_Content)
		    foreach(func_get_args() as $Content)
			    $this->addContent($Content);
    }

    function addContent(IRenderHTML $Content) {
        $this->mContent[] = $Content;
    }

    function add($paramName, $description=null, $defaultValue=null) {
        $this->addContent(new Parameter($paramName, $description, $defaultValue));
    }

    function req($paramName, $description=null, $defaultValue=null) {
        $this->addContent(new RequiredParameter($paramName, $description, $defaultValue));
    }

	/**
	 * Validate a form request
	 * @param IRequest $Request
	 * @throws $this
	 * @return mixed the validated data
	 */
	function validateRequest(IRequest $Request) {
        $values = array();
        /** @var RequestException[] $Exs */
        $Exs = array();
		$c = 0;
        foreach($this->mContent as $Content) {
            if($Content instanceof IRequestParameter) {
                try {
	                //$value = $Request->getValue($Content);
                    $return = $Content->validateRequest($Request);
	                if(is_array($return))
		                foreach($return as $k=>$v)
			                $values[$k] = $v;
	                else
                        $values[$Content->getName()] = $return;
	                $c++;
                } catch (RequestException $ex) {
                    $Exs[] = $ex;
	                $values[$Content->getName()] = null;
                }
            }
        }

        if($Exs) {
            $msg = sizeof($Exs) . " Exception(s) occurred during validation: ";
            foreach($Exs as $ex)
                $msg .= "\n\t" . $ex->getMessage();
            throw $this->updateResponse($msg, IResponse::HTTP_ERROR);
        }

		$this->updateResponse("Validation succeeded ({$c})", IResponse::HTTP_SUCCESS);
        return $values;
    }

	function updateResponse($message, $code=null) {
		if($code !== null)
			$this->code = $code;
		$this->message = $message;
		return $this;
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeScript(__DIR__ . '\assets\form-validation.js');
        $Head->writeStyleSheet(__DIR__ . '\assets\form-validation.css');
		$Logger = new StaticLogger;
		$Logger->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param Attribute\IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$Form = new HTMLForm('POST');
		$Form->addClass('form-validation');
		$Legend = new HTMLElement('legend', null, $this->getMessage());
		$Form->addContent($Legend);

		foreach($this->mContent as $Content)
			$Form->addContent($Content);

		$Form->addSubmit();
		$Form->renderHTML($Request, $Attr);

		$Logger = new StaticLogger;
		if($Logger::hasLog())
			$Logger->renderHTML($Request);
	}
}

