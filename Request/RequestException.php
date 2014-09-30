<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 3:00 PM
 */
namespace CPath\Request;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Element\HTMLForm;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Render\HTML\Element\HTMLLabel;
use CPath\Render\HTML\Element\HTMLTextAreaField;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Log\StaticLogger;
use CPath\Response\Exceptions\HTTPRequestException;
use CPath\Response\IHeaderResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseCode;

class RequestException extends HTTPRequestException implements IRenderHTML, IResponse, IHTMLSupportHeaders
{
    private $mFormMethod = null;
    /**
     * @param string $message
     * @param null $statusCode
     * @param null $_arg [varargs] used to format the exception
     */
    function __construct($message, $statusCode=null, $_arg=null) {
        if($_arg !== null)
            $message = vsprintf($message, array_slice(func_get_args(), 2));
        parent::__construct($message, $statusCode);
    }

    public function setFormMethod($method) {
        $this->mFormMethod = $method;
    }

//    /**
//     * Map data to a data map
//     * @param IKeyMap $Map the map instance to add data to
//     * @return void
//     */
//    function mapKeys(IKeyMap $Map) {
//        $THIS = $this;
//        $Map->map('param', new MappableCallback(function(IKeyMap $Map) use ($THIS) {
//            foreach($THIS->getAllDescriptions() as $param => $desc)
//                $Map->map($param, $desc);
//        }));
//    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeScript(__DIR__ . '/assets/request-exception.js');
        $Head->writeStyleSheet(__DIR__ . '/assets/request-exception.css');

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
        $Form = $this->getForm($Request);
        $Form->renderHTML($Request, $Attr);

        $Logger = new StaticLogger;
        if($Logger::hasLog())
            $Logger->renderHTML($Request);
    }

    protected function getForm(IRequest $Request, HTMLForm $Form=null) {
        $Form = $Form ?: new HTMLForm($this->mFormMethod ?: 'POST');
        if($Request->getMethodName() !== 'GET') {
            $Legend = new HTMLElement('legend', null, $this->getMessage());
            $Form->addContent($Legend);
        }
        $Form->addClass('request-exception');
        foreach($Request->getParameterDescriptions() as $paramName=>$desc) {
            list($desc, $flags) = $desc;
            $value = $Request->getValue($paramName);
            if($flags & IRequest::PARAM_TEXTAREA)
                $Input = new HTMLTextAreaField($value);
            else
                $Input = new HTMLInputField($value);
            $Input->setAttribute('name', $paramName);

            $Label = new HTMLLabel($desc);

            if($flags & IRequest::PARAM_REQUIRED)
                $Label->addClass('required');
            if($flags & IRequest::PARAM_ERROR)
                $Label->addClass('error');

            $Label->addContent($Input);
            $Form->addContent($Label);
        }
        $Form->addSubmit();
        return $Form;
    }
}