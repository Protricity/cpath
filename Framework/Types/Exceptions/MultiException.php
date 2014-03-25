<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Types\Exceptions;

use CPath\Framework\API\Exceptions\ValidationException;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Response\Types\ExceptionResponse;

class MultiException extends \Exception implements \Countable, IMappable {
    /** @var \Exception[]  */
    private $mEx = array();

    public function add(\Exception $ex, $fieldName=null) {
        if($ex instanceof ValidationException)
            $ex = $ex->getFieldError($fieldName); // TODO: fix
        else if($ex instanceof \Exception)
            $ex = $ex->getMessage();
        $this->message = ($this->message ? $this->message."\n" : "") . $ex;
        $this->mEx[] = $ex;
    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map) {
        foreach($this->mEx as $ex)
            $Map->mapArrayObject(new ExceptionResponse($ex));
    }

    public function count() {
        return count($this->mEx);
    }

//    /**
//     * Render this request
//     * @param IRequest $Request the IRequest instance for this render
//     * @return String|void always returns void
//     */
//    function render(IRequest $Request) {
//        $Render = new RenderMimeSwitchUtility($this);
//        $Render->render($Request);
//    }
//
//
//    function renderText(IRequest $Request) {
//        $max = 0;
//        foreach($this->mEx as $field => $ex)
//            if(strlen($field) > $max) $max = strlen($field);
//        foreach($this->mEx as $field => $ex)
//            echo "(", str_pad($field, $max, ' '), ") ", $ex, "\n";
//    }
//
//    /**
//     * Render request as JSON
//     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
//     * @return void
//     */
//    function renderJSON(IRequest $Request) {
//        $i=0;
//        echo '[';
//        foreach($this->mEx as $field => $ex) {
//            echo "'message':", json_encode($ex),
//                " 'field':", json_encode($field),
//                '}';
//            if($i++)
//                echo ',';
//        }
//        echo ']';
//    }
//
//    /**
//     * Render request as xml
//     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
//     * @param string $rootElementName Optional name of the root element
//     * @return void
//     */
//    function renderXML(IRequest $Request, $rootElementName = 'exception') {
//        foreach($this->mEx as $field => $ex)
//            echo RI::ni(), "<", $rootElementName, " field='{$field}'>", htmlentities($ex), "</", $rootElementName, ">";
//    }
//
//    /**
//     * Render request as html and sends headers as necessary
//     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
//     * @param IAttributes $Attr optional attributes for the input field
//     * @return void
//     */
//    function renderHtml(IRequest $Request, IAttributes $Attr = null) {
//        $max = 0;
//        foreach($this->mEx as $field => $ex)
//            if(strlen($field) > $max) $max = strlen($field);
//        foreach($this->mEx as $field => $ex)
//            echo "(", str_pad($field, $max, ' '), ") ", $ex, "<br />";
//    }
}