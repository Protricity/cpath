<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Builders\RouteBuilder;
use CPath\Config;
use CPath\Handlers\Api\Interfaces\APIException;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Api\Interfaces\IParam;
use CPath\Handlers\Layouts\NavBarLayout;
use CPath\Handlers\Layouts\PageLayout;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Helpers\Describable;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IRoute;
use CPath\Log;
use CPath\Misc\RenderIndents as RI;
use CPath\Model\ExceptionResponse;
use CPath\Util;

class APIView extends NavBarLayout implements ILogListener {

    const BUILD_IGNORE = true;
    private $mLog = array();
    private $mAPI = null;
    private $mRoute = null;
    private $mResponse = null;

    public function __construct(ITheme $Theme=null, IAPI $API=null, IRoute $Route=null, IResponse $Response=null) {
        parent::__construct($Theme ?: CPathDefaultTheme::get());
        $this->mAPI = $API;
        $this->mRoute = $Route;
        $this->mResponse = $Response;

        if(Config::$Debug)
            Log::addCallback($this);
    }

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    protected function setupHeadFields() {
        parent::setupHeadFields();
        $basePath = Base::getClassPublicPath($this, false);
        $this->addHeadStyleSheet($basePath . 'assets/apiview.css');
        $this->addHeadScript($basePath . 'assets/apiview.js');
    }

    function getAPIFromRequest(IRequest $Request) {
        if($this->mAPI)
            return $this->mAPI;

        if(!$apiClass = $Request->getNextArg())
            throw new APIException("No API Class passed to ".__CLASS__);

        $API = new $apiClass();
        if($API instanceof IHandlerAggregate)
            $API = $API->getAggregateHandler();

        if(!($API instanceof IAPI))
            throw new APIException($apiClass. " is not an instance of IAPI");

        $routes = $API->getAllRoutes(new RouteBuilder());
        $this->mRoute = current($routes);

        return $this->mAPI = $API;
    }

    function getAPIRouteFromRequest(IRequest $Request) {
        if($this->mRoute)
            return $this->mRoute;

        $API = $this->getAPIFromRequest($Request);
        $routes = $API->getAllRoutes(new RouteBuilder());
        $this->mRoute = current($routes);

        return $this->mRoute;
    }

    /**
     * Render the main view content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderViewContent(IRequest $Request)
    {
        $API = $this->getAPIFromRequest($Request);
        $Route = $this->getAPIRouteFromRequest($Request);
        $Response = $this->mResponse;

        if($Response == NULL && strcasecmp($Request->getMethod(), 'get') !== 0)
            $Response = $API->execute($Request);

        //$basePath = Base::getClassPublicPath($this);

        $domainPath = Config::getDomainPath();
        $route = $Route->getPrefix();
        list($method, $path) = explode(' ', $route, 2);
        $path = rtrim($domainPath, '/') . $path;

        foreach($API->getFields() as $name=>$Field)
            if($Field instanceof IParam)
                $route .= '/:'.$name;
        $num = 1;

        $fields = $API->getFields();

        RI::get()->setIndent(0, "    ");
        ?>
        <h3>Params:</h3>
        <form class="field-form" method="POST" enctype="multipart/form-data">
            <ul class='field-table'>
                <li class='field-header clearfix'>
                    <div class='field-num'>#</div>
                    <div class='field-required'>Req'd</div>
                    <div class='field-name'>Name</div>
                    <div class='field-description'>Description</div>
                    <div class='field-input'>Test</div>
                </li>
                <?php if(!$fields) { ?>
                <li class='field-item clearfix'>
                    <div class='field-num'>&nbsp;</div>
                    <div class='field-required'>&nbsp;</div>
                    <div class='field-name'>&nbsp;</div>
                    <div class='field-description'>No Fields in this API</div>
                    <div class='field-input'>&nbsp;</div>
                </li>
                <?php } else foreach($fields as $name=>$Field) { echo "\n"; ?>
                <li class='field-item clearfix'>
                    <div class='field-num'><?php echo $num++; ?>.</div>
                    <div class='field-required'><?php echo $Field->isRequired() ? 'yes' : '&nbsp;'; ?></div>
                    <div class='field-name'><?php echo $name; ?></div>
                    <div class='field-description'><?php echo Describable::get($Field)->getDescription(); ?></div>
                    <div class='field-input'><?php
                            RI::si(5);
                            $Field->render($Request);
                            echo "\n"; ?>
                    </div>
                </li><?php } echo "\n"; ?>
                <li class='field-footer clearfix'>
                    <div class='field-num'></div>
                    <div class='field-required'></div>
                    <div class='field-name'></div>
                    <div class='field-description'></div>
                    <div class='field-input'></div>
                </li>
                <li class='field-footer-buttons'>
                    <input type="button" value="JSON" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'json', '<?php echo $method; ?>');" />
                    <input type="button" value="XML" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'xml', '<?php echo $method; ?>');" />
                    <input type="button" value="TEXT" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'text', '<?php echo $method; ?>');" />
                    <input type="submit" value="POST"/>
                    <input type="button" value="JSON Object (POST)" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'json', 'POST', true);" />
                    <span id="spanCustom" style="display: none">
                        <label>Accepts:
                            <input  id="txtCustomText" type="text" value="*/*" size="4" />
                        </label>
                        <label>Method:
                            <input id="txtCustomMethod" type="text" value="GET" size="4" />
                        </label>
                    </span>
                    <input id="btnCustomSubmit" type="button" value="Custom" onmousemove="jQuery('#spanCustom').fadeIn();" onclick="APIView.submit('<?php echo $path; ?>', this.form, '', jQuery('#txtCustomMethod').val(), false, jQuery('#txtCustomText').val());" />
                    <input type="button" value="Update URL" onclick="APIView.updateURL(this.form);" />
                </li>
            </ul>
            <div>
            </div>
        </form>
        <div class="response-container" style="<?php if(!$Response) echo 'display: none'; ?>">
            <h3>Response</h3>
            <div class='response-content'>
                <?php
                if($Response) {
                    try{
                        $JSON = Util::toJSON($Response);
                        echo json_encode($JSON);
                    } catch (\Exception $ex) {
                        $Response = new ExceptionResponse($ex);
                        $JSON = Util::toJSON($Response);
                        echo json_encode($JSON);
                    }
                }
                ?>
            </div>
            <h3>Response Headers</h3>
            <div class='response-headers'></div>
            <h3>Request Headers</h3>
            <div class='request-headers'></div>
        </div>
    <?php if(false && Config::$Debug) { ?>
        <h3>Debug</h3>
        <table><?php
            /** @var ILogEntry $log */
            //foreach($this->mLog as $log)
            //    echo "<tr><td>",$log->getTag(),"</td><td style='white-space: pre'>{$log}</td></tr>";

            ?></table>
    <?php } echo "\n";
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyHeaderContent(IRequest $Request)
    {
        $API = $this->getAPIFromRequest($Request);
        $Route = $this->getAPIRouteFromRequest($Request);
        $route = $Route->getPrefix();
        echo RI::ni(), "<h1>{$route}</h1>";
        echo RI::ni(), "<h2>", Describable::get($API)->getDescription(), "</h2>";
        if(!empty($_SERVER['HTTP_REFERER']))
            echo RI::ni(), "<h4><a href=", $_SERVER['HTTP_REFERER'], ">Go Back</a></h4>";
    }

    /**
     * Render the navigation bar content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderNavBarContent(IRequest $Request)
    {
        // TODO: Implement renderNavBarContent() method.
    }

    /**
     * Render the header
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    protected function renderBodyFooterContent(IRequest $Request)
    {
        // TODO: Implement renderBodyFooterContent() method.
    }
}
