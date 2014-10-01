<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 6:26 PM
 */
namespace CPath\Request\Parameter;

use CPath\Describable\Describable;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Element\HTMLForm;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Log\StaticLogger;

class RequestParameterForm implements IRenderHTML, IHTMLSupportHeaders
{
    private $mMap;

    public function __construct(IParameterMap $Parameters=null) {
        $this->mMap = $Parameters;
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeScript(__DIR__ . '/assets/request-parameter-form.js');
        $Head->writeStyleSheet(__DIR__ . '/assets/request-parameter-form.css');

        $Logger = new StaticLogger;
        $Logger->writeHeaders($Request, $Head);
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Map = $this->mMap ?: $Request;

        $Form = new HTMLForm('POST');
        $Legend = new HTMLElement('legend', null, Describable::get($Map)->getDescription());
        $Form->addContent($Legend);

        if ($Request->getMethodName() !== 'GET') {
        }

        $Form->addClass('request-parameter-form');

        $Map->mapParameters(
            new MappableParameterCallback(
                function (Parameter $Parameter) use ($Form) {
                    $Form->addContent($Parameter);
                }
            )
        );

        $Form->addSubmit();
        $Form->renderHTML($Request, $Attr);

        $Logger = new StaticLogger;
        if($Logger::hasLog())
            $Logger->renderHTML($Request);
    }
}