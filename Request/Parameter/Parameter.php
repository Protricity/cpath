<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:45 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Render\HTML\Element\HTMLLabel;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\RequestException;

class Parameter implements IRenderHTML
{
    private $mName;
    private $mDescription;

    public function __construct($paramName, $description=null) {
        $this->mName = $paramName;
        $this->mDescription = $description;
    }

    /**
     * Get the request parameter name
     * @return String
     */
    function getName() {
        return $this->mName;
    }

    /**
     * Get the request parameter name
     * @return String
     */
    function getDescription() {
        return $this->mDescription;
    }

    /**
     * Validate this request or throw a ValidationException
     * @param IRequest $Request
     * @throws RequestException
     * @return mixed validated value
     */
    function validateRequest(IRequest $Request) {
        return $Request->getValue($this->getName(), $this->getDescription());
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Input = new HTMLInputField();
        $Input->setName($this->mName);

        $Label = new HTMLLabel($this->getDescription());

        try {
            $value = $this->validateRequest($Request);
            $Input->setValue($value);

        } catch (RequestException $ex) {
            //$Label->addClass('error');
            if($Request->getMethodName() === 'POST')
                $Label->addContent(new HTMLElement('div', 'class=error', $ex->getMessage()));

        }

        $Label->addContent($Input);
        $Label->renderHTML($Request, $Attr);
    }
}