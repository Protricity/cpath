<?php
namespace CPath\Framework\Render\Layout\API;

use CPath\Base;
use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\API\Fragments\APIDebugFormFragment;
use CPath\Framework\API\Fragments\APIResponseBoxFragment;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\Render\Fragment\Common\HTMLFragment;
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Render\Layout\Template\Abstract3SectionLayout;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\View\Templates\Layouts\NavBar\AbstractNavBarLayout;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;
use CPath\Response\IResponse;
use CPath\Templates\Themes\CPathDefaultTheme;

class APILayout extends Abstract3SectionLayout implements IRenderAll, IAPI {

    private $mAPI = null;
    private $mForm, $mResponseBox;
    private $mResponse = null;

    public function __construct(IAPI $API, IResponse $Response=null, ITheme $Theme=null) {
        parent::__construct($Theme ?: CPathDefaultTheme::get());

        $this->mAPI = $API;
        $this->mResponse = $Response;

        $this->mForm = new APIDebugFormFragment($this->mAPI);
        $this->mResponseBox = new APIResponseBoxFragment($this->getTheme());

        $this->addBodyFragment($this->mForm);
        $this->addBodyFragment($this->mResponseBox);

        $this->addHeaderFragment(new HTMLFragment(function(IRequest $Request, IAttributes $Attr) use ($API) {
            $route = $Request->getMethodName() . ' ' . $Request->getPath();
            echo RI::ni(), "<h1>{$route}</h1>";
            echo RI::ni(), "<h2>", Describable::get($API)->getDescription(), "</h2>";
        }));

    }
//    /**
//     * Return an instance of IRender
//     * @param IRequest $Request the IRequest instance for this render
//     * @return IRender return the renderer instance
//     */
//    function getRenderer(IRequest $Request) {
//        $this->mArgs = $Request->getArgs();
//        return parent::getRenderer($Request, $path, $args);
//    }

    /**
     * Render the navigation bar content
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
//        $RouteSet = $Request->getRoute();
//        if($RouteSet instanceof RoutableSet) {
//            $Util = new RouteUtil($RouteSet);
//            $Routes = $RouteSet->getRoutes();
//
//            $ids = $this->getRoutableSetIDs($RouteSet);
//            foreach($ids as $i=>$prefix) {
//                /** @var IRoute $Route */
//                $Route = $Routes[$prefix];
//                $Handler = $Route->loadHandler();
//                if(!$Handler instanceof IAPI)
//                    continue;
//                $Describable = Describable::get($Handler);
//                $this->renderNavBarEntry($Util->buildPublicURL(true) . '/' . $i . '#' . $Route->getPrefix(), $Describable);
//            }
//        }
    }

    /**
     * Render request as JSON
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request)
    {
        $Response = $this->execute($Request);
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderJSON($Request, true);
    }

    /**
     * Render request as plain text
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderText(IRequest $Request)
    {
        $Response = $this->execute($Request);
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderText($Request, true);
    }

    /**
     * Render request as xml
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root')
    {
        $Response = $this->execute($Request);
        $ResponseUtil = new ResponseUtil($Response);
        $ResponseUtil->renderXML($Request, $rootElementName, true);
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and args
     * @internal param Array $args additional arguments for this execution
     * @return IResponse the api call response with data, message, and status
     */
    function execute(IRequest $Request) {
        return $this->mAPI->execute($Request);
    }

    /**
     * Get all API Fields
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IField[]|IFieldCollection
     */
    function getFields(IRequest $Request) {
        return $this->mAPI->getFields($Request);
    }
}
