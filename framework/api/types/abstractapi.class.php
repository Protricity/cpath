<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Types;

use CPath\Base;
use CPath\Describable\IDescribableAggregate;
use CPath\Framework\Api\Interfaces\APIException;
use CPath\Framework\Api\Interfaces\APIUtil;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Api\Interfaces\IField;
use CPath\Framework\Api\Validation\Interfaces\IValidation;
use CPath\Framework\Api\Interfaces\ValidationException;
use CPath\Framework\Api\Interfaces\ValidationExceptions;
use CPath\Framework\CLI\Option\Interfaces\IOptionMap;
use CPath\Framework\CLI\Option\Interfaces\OptionMissingException;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Views\APIView;
use CPath\Interfaces\IExecute;
use CPath\Interfaces\IHandler;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Interfaces\IViewConfig;
use CPath\Response\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Route\RoutableSet;
use CPath\Util;

/**
 * Class API
 * @package CPath
 *
 * Provides a Handler template for API calls
 */
abstract class AbstractAPI implements IAPI, IHandler, IViewConfig, IDescribableAggregate, IOptionMap {

    const BUILD_IGNORE = false;             // API Calls are built to provide routes
    const LOG_ENABLE = true;                // Enable API Logging
    const ROUTE_API_VIEW_TOKEN = ':api';    // Add an APIView route entry i.e. ':api' for this API on GET requests

    const ROUTE_METHOD = 'POST';            // Default accepted method is POST
    const ROUTE_PATH = NULL;                // No custom route path. Path is based on namespace + class name

    /** @var IField[] */
    private $mColumns = array();
    /** @var IValidation[] */
    private $mValidations = array();

    private $mSetup = false;

    private $mUtil = null;

    private $mOption = array();

    public function __construct() {
    }

    private function getUtil() { return $this->mUtil ?: $this->mUtil = new APIUtil($this); }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        $basePath = Base::getClassPublicPath(__CLASS__, false);
        $View->addHeadScript($basePath . 'assets/api.js', true);
        $View->addHeadStyleSheet($basePath . 'assets/api.css');
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    abstract protected function setupAPI();

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws APIException if an exception occurs
     */
    abstract protected function doExecute(IRequest $Request);


    private function _setupFields() {
        if($this->mSetup)
            return;
        $this->mSetup = true;
        $this->setupAPI();
        return;
    }

    /**
     * Execute this API Endpoint with the entire request returning an IResponse object or throwing an exception
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    final public function executeOrThrow(IRequest $Request) {
        $Util = $this->getUtil();
        if($this instanceof IExecute)
            $this->onAPIPreExecute($Request);

        $this->processRequest($Request);
        return $Util->executeOrThrow($Request);
    }


    /**
     * Execute this API Endpoint with the entire request returning an IResponse object
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse the api call response with data, message, and status
     */
    final public function execute(IRequest $Request) {
        $Util = $this->getUtil();
        if($this instanceof IExecute)
            $this->onAPIPreExecute($Request);

        return $Util->execute($Request);
    }

    /**
     * Render this API Call. The output format is based on the requested mimeType from the browser
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function render(IRequest $Request) {
        foreach($Request->getMimeTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    $this->renderJSON($Request);
                    return;
                case 'application/xml':
                    $this->renderXML($Request);
                    return;
                case 'text/html':
                    $this->renderHTML($Request);
                    return;
                case 'text/plain':
                    $this->renderText($Request);
                    return;
            }
        }
        $this->renderDefault($Request);
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as HTML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderHTML(IRequest $Request) {
        if(!headers_sent() && !Base::isCLI())
            header("Content-Type: text/html");
        $Response = null;
        //if(strcasecmp($Request->getMethod(), 'get') !== 0) //TODO: did we decide how to handle posts from a browser?
        //    $Response = $this->execute($Request);
        //if($Request instanceof RoutableSetWrapper) {
        //    $RoutableSet = $Request->getRoutableSet();
         //   $Render = new APIMultiView($RoutableSet, $Response);
        //} else {
            $Route = $Request->getRoute();
            $Render = new APIView($this, $Route, $Response);
        //}
        $Render->render($Request);
        //$Response = $this->execute($Route);
        //$Response->sendHeaders();
        //$Response->renderHtml();
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderJSON(IRequest $Request) {
        if(!headers_sent()) // && !Base::isCLI())
            header("Content-Type: application/json");
        $Response = $this->execute($Request);
        $Response->sendHeaders();
        try{
            $JSON = Util::toJSON($Response);
            echo json_encode($JSON);
        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);
            $JSON = Util::toJSON($Response);
            echo json_encode($JSON);
        }
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderXML(IRequest $Request) {
        if(!headers_sent()) // && !Base::isCLI())
            header("Content-Type: text/xml");
        $Response = $this->execute($Request);
        $Response->sendHeaders();
        try{
            $XML = Util::toXML($Response);
            echo $XML->asXML();
        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);
            $XML = Util::toXML($Response);
            echo $XML->asXML();
        }
    }

    /**
     * Sends headers, executes the request, and renders an IResponse as Plain Text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderText(IRequest $Request) {
        $Response = $this->execute($Request);
        $Response->sendHeaders('text/plain');
        $Response->renderText();
    }

    /**
     * Renders via default method
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderDefault(IRequest $Request) {
        $this->renderText($Request);
    }

    /**
     * Add an API Field.
     * @param IField $Field Describes the Field. Implement IField for custom validation
     * @param null $shortOpts
     * @throws \InvalidArgumentException
     * @internal param null $_shortOptions
     * @internal param bool|int $prepend Set true to prepend
     * @internal param string $name name of the Field
     * @return $this Return the class instance
     * @throw \InvalidArgumentException if the field already exist, or is replacing a field that does not exist
     */
    protected function addField(IField $Field, $shortOpts = null) {
        $name = $Field->getName();

        if(isset($this->mColumns[$name]))
            throw new \InvalidArgumentException("Field {$name} already exists.");

//        if(strpos($name, ':') !== false) {
//            list($name, $shorts) = explode(':', $name, 2);
//            foreach(explode(';', $shorts) as $short)
//                $Field->addShortName($short);
//        }

        $this->_setupFields();
//        if($prepend) {
//            $old = $this->mColumns;
//            $this->mColumns = array();
//            $this->mColumns[$name] = $Field;
//            foreach($old as $k=>$v)
//                $this->mColumns[$k] = $v;
//        } else {

        $this->mColumns[$name] = $Field;


        if($shortOpts) {
            $shortOpts = is_array($shortOpts) ? $shortOpts : preg_split('/[^\w]/', $shortOpts);
            foreach($shortOpts as $opt)
                $this->addShortOption($opt, $name);
        }


        //}
        return $this;
    }

    /**
     * Add an array of API Fields
     * @param IField[] $fields associative array of Fields.
     * The array key represents the Field name.
     * @return $this return the class instance
     */
    protected function addFields(Array $fields) {
        $this->_setupFields();
        foreach($fields as $Field)
            $this->addField($Field);
        return $this;
    }

    /**
     * Get all API Fields
     * @return IField[]
     */
    public function getFields() {
        $this->_setupFields();
        return $this->mColumns;
    }

//    /**
//     * Get an API field by name
//     * @param String $fieldName the field name
//     * @return IField
//     * @throws FieldNotFound if the field was not found
//     */
//    public function getField($fieldName) {
//        $this->_setupFields();
//        if(!isset($this->mColumns[$fieldName]))
//            throw new FieldNotFound("Field '{$fieldName}' is not in this API");
//        return $this->mColumns;
//    }

    /**
     * Add a validation
     * @param IValidation $Validation the validation
     * @return $this Return the class instance
     */
    function addValidation(IValidation $Validation){
        $this->mValidations[] = $Validation;
        return $this;
    }

    /**
     * Process a request. Validates each Field. Provides optional Field formatting
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return void
     * @throws ValidationExceptions if one or more Fields fail to validate
     */
    protected function processRequest(IRequest $Request) {
        $this->_setupFields();

        $Util = new APIUtil($this);
        $Util->processRequest($Request);

        $FieldExceptions = new ValidationExceptions($this);

        foreach($this->mValidations as $Validation) {
            try {
                $Validation->validate($Request);
            } catch (ValidationException $ex) {
                $FieldExceptions->addFieldException(null, $ex);  // TODO: null?
            }
        }

        if(count($FieldExceptions))
            throw $FieldExceptions;
    }


    /**
     * Returns the default IHandlerSet collection for this API.
     * @return RoutableSet a set of common routes for this API
     */
    protected function loadDefaultRouteSet() {
        $Routes = RoutableSet::fromHandler($this);
        APIView::addRoutes($Routes, $this, static::ROUTE_API_VIEW_TOKEN);
        $methods = explode(',', static::ROUTE_METHOD);
        foreach($methods as $method)
            $Routes[$method] = $this;
        return $Routes;
    }

    /**
     * Match an option against a map and return the value if found
     * @param $option
     * @return String
     * @throws OptionMissingException if the option was not found
     */
    function matchOption($option) {
        foreach($this->mOption as $key => $field)
            if($key === $option)
                return $field;
        throw new OptionMissingException("Option was not found: " . $option);
    }

    function addShortOption($shortOption, $targetField) {
        $this->mOption[$shortOption] = $targetField;
    }
}


