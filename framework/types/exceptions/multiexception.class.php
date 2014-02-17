<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Types\Exceptions;

use CPath\Framework\Render\Interfaces\IAttributes;
use CPath\Framework\Render\Interfaces\IRenderAll;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Util\RenderIndents as RI;

class MultiException extends \Exception implements \Countable, IRenderAll {
    private $mEx = array();
    public function add($ex, $key=NULL) {
        if($ex instanceof \Exception)
            $ex = $ex->getMessage();
        $this->message = ($this->message ? $this->message."\n" : "") . $ex;
        if(is_int($key))
            $this->mEx[] = $ex;
        else
            $this->mEx[$key] = $ex;
    }

    public function count() {
        return count($this->mEx);
    }

    function toJSON(Array &$JSON) {
        $JSON['errors'] = array();
        foreach($this->mEx as $field => $ex)
            $JSON['errors'][] = array('msg' => $ex, 'field' => $field);
    }

    function toXML(\SimpleXMLElement $xml) {
        $rxml = $xml->addChild('errors');
        foreach($this->mEx as $field => $ex)
            $rxml->addChild('error', $ex)
                ->addAttribute('field', $field);
    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request)
    {
        $this->renderHtml($Request);
    }


    function renderText(IRequest $Request) {
        $max = 0;
        foreach($this->mEx as $field => $ex)
            if(strlen($field) > $max) $max = strlen($field);
        foreach($this->mEx as $field => $ex)
            echo "(", str_pad($field, $max, ' '), ") ", $ex, "\n";
    }

    /**
     * Render request as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function renderJSON(IRequest $Request) {
        $i=0;
        echo '[';
        foreach($this->mEx as $field => $ex) {
            echo "'message':", json_encode($ex),
                " 'field':", json_encode($field),
                '}';
            if($i++)
                echo ',';
        }
        echo ']';
    }

    /**
     * Render request as xml
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return void
     */
    function renderXML(IRequest $Request, $rootElementName = 'exception') {
        foreach($this->mEx as $field => $ex)
            echo RI::ni(), "<", $rootElementName, " field='{$field}'>", htmlentities($ex), "</", $rootElementName, ">";
    }

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr = null) {
        $max = 0;
        foreach($this->mEx as $field => $ex)
            if(strlen($field) > $max) $max = strlen($field);
        foreach($this->mEx as $field => $ex)
            echo "(", str_pad($field, $max, ' '), ") ", $ex, "<br />";
    }
}