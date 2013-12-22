<?php
namespace CPath\Handlers\Views;

use CPath\Config;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Describable\Describable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Request\Web;
use CPath\Route\RouteSet;

class APIMultiView extends APIView {
    private $mHandlers=array();
    /** @var IAPI[] */
    private $mAPIs=array();
    private $mSelectedAPI;

    /**
     * @param Array|IHandler[]|RouteSet $Handlers
     * @param IResponse $Response
     * @param ITheme $Theme
     * @throws \InvalidArgumentException
     */
    public function __construct($Handlers, IResponse $Response=null, ITheme $Theme=null) {
        $this->mHandlers = $Handlers;
        foreach($Handlers as $Handler)
            if($Handler instanceof IAPI)
                $this->mAPIs[] = $Handler;
        if(!$this->mAPIs)
            throw new \InvalidArgumentException("No APIs found in handlers");
        if($Handlers instanceof RouteSet && $Handlers->hasDefault())
            $this->mSelectedAPI = $Handlers->getDefault();
        else
            $this->mSelectedAPI = $this->mAPIs[0];
        $SelectAPI = $this->mSelectedAPI;
//        if($id = Web::fromRequest()->getNextArg()) {
//            if(isset($this->mAPIs[$id]))
//                throw new \InvalidArgumentException("API #{$id} was not found");
//            $SelectAPI = $this->mAPIs[$id];
//        }
        parent::__construct($this->mSelectedAPI, $Response, $Theme);
    }


    function getAPI() { return $this->mSelectedAPI; }


    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request) {
        foreach($this->mAPIs as $i=>$API) {
            $this->renderNavBarEntry($Request->getRequestURL(true) . '/' . $i . '#' . $API->getDescribable(), $API);
        }
    }

}
