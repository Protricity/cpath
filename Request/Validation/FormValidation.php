<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 4:16 PM
 */
namespace CPath\Request\Validation;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLFormAjaxSupport;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Parameter\IRequestParameter;


class FormValidation implements IRenderHTML, IHTMLSupportHeaders {
	/** @var IRenderHTML[] */
    private $mContent = array();
    public function __construct($_Content=null) {
	    if($_Content)
		    foreach(func_get_args() as $Content)
			    if($Content instanceof IRequestParameter)
				    $this->addParameter($Content);
				else
				    $this->addContent($Content);
    }
//
//	/**
//	 * Validate a form request and returns the values
//	 * @param IRequest $Request
//	 * @param null $paramName
//	 * @throws Exceptions\FormValidationException
//	 * @return array
//	 */
//	function validateRequest(IRequest $Request, $paramName=null) {
//        $values = array();
//        /** @var RequestException[] $Exs */
//        $Exs = array();
//		$c = 0;
//        foreach($this->mContent as $Content) {
//	        if($Content instanceof IRequestParameter) {
//		        $value = $Content->getInputValue($Request);
//		        if($Content instanceof IValidation) {
//	                try {
//	                    $return = $Content->validate($Request, $value);
//		                /** @var IRequestParameter $Content */
//		                if(is_array($return))
//			                foreach($return as $k=>$v)
//				                $values[$k] = $v;
//		                else
//	                        $values[$Content->getName()] = $return;
//		                $c++;
//	                } catch (RequestException $ex) {
//	                    $Exs[] = $ex;
//		                $values[$Content->getName()] = null;
//	                }
//	            } else {
//			        $values[$Content->getName()] = $value;
//		        }
//	        }
//        }
//
//        if($Exs) {
//	        $message = sizeof($Exs) . " Exception(s) occurred during validation: \n\t" . implode("\n\t", $Exs);
//	        $Exception = new FormValidationException($this, $message, IResponse::HTTP_ERROR, $Exs[0]);
//	        throw $Exception;
//        }
//
//		if($paramName)
//			return $values[$paramName];
//		return $values;
//    }

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeScript(__DIR__ . '\assets\form-validation.js');
        $Head->writeStyleSheet(__DIR__ . '\assets\form-validation.css');
		$Form = new HTMLFormAjaxSupport('POST');
		$Form->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param Attribute\IAttributes $Attr
	 * @param \CPath\Render\HTML\IRenderHTML|\CPath\Request\Validation\IHTMLContainer $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Form = new HTMLFormAjaxSupport('POST');
		$Form->addClass('form-validation');
//		$Legend = new HTMLElement('legend', null, $this->getMessage());
//		$Form->addContent($Legend);

		foreach($this->mContent as $Content) {
			if($Content instanceof IRequestParameter)
				$Content = new \CPath\Request\Parameter\HTML\HTMLFormItemTemplate($Content);
			$Form->addContent($Content);
		}

		$Form->addSubmit();
		$Form->renderHTML($Request, $Attr, $Parent);

	}
}


