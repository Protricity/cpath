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
use CPath\Handlers\Themes\Util\TableThemeUtil;
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

    public function __construct(IAPI $API=null, IRoute $Route=null, IResponse $Response=null, ITheme $Theme=null) {
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

        $Table = new TableThemeUtil($Request, $this->getTheme());

        echo RI::ni(), "<form class='apiview-form' method='POST' enctype='multipart/form-data'>";
        RI::ai(1);

            $Table->renderStart(Describable::get($API)->getDescription(), 'apiview-table');
                $Table->renderHeaderStart();
                    $Table->renderTD('#',           0, 'table-field-num');
                    $Table->renderTD('Req\'d',      0, 'table-field-required');
                    $Table->renderTD('Name',        0, 'table-field-name');
                    $Table->renderTD('Description', 0, 'table-field-description');
                    $Table->renderTD('Test',        0, 'table-field-input');
            if(!$fields) {
                $Table->renderRowStart();
                    $Table->renderTD('&nbsp;',      0, 'table-field-num');
                    $Table->renderTD('&nbsp;',      0, 'table-field-required');
                    $Table->renderTD('&nbsp;',      0, 'table-field-name');
                    $Table->renderTD('&nbsp;',      0, 'table-field-description');
                    $Table->renderTD('&nbsp;',      0, 'table-field-input');
            } else foreach($fields as $name=>$Field) {
                $req = $Field->isRequired() ? 'yes' : '&nbsp;';
                $desc = Describable::get($Field)->getDescription();;

                $Table->renderRowStart();
                $Table->renderTD($num++,    0, 'table-field-num');
                $Table->renderTD($req,      0, 'table-field-required');
                $Table->renderTD($name,     0, 'table-field-name');
                $Table->renderTD($desc,     0, 'table-field-description');
                $Table->renderDataStart(    0, 'table-field-input');
                    $Field->render($Request);
            }
    //        $Table->renderFooterStart();
    //            $Table->renderTD('', 0, 'table-field-num');
    //            $Table->renderTD('', 0, 'table-field-required');
    //            $Table->renderTD('', 0, 'table-field-name');
    //            $Table->renderTD('', 0, 'table-field-description');
    //            $Table->renderTD('', 0, 'table-field-input');

            $Table->renderFooterStart();
                $Table->renderDataStart(5, 'table-field-footer-buttons', "style='text-align: left'");
            ?>

                                    <input type="button" value="JSON" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'json', '<?php echo $method; ?>');" />
                                    <input type="button" value="XML" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'xml', '<?php echo $method; ?>');" />
                                    <input type="button" value="TEXT" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'text', '<?php echo $method; ?>');" />
                                    <input type="submit" value="POST"/>
                                    <input type="button" value="JSON Object (POST)" onclick="APIView.submit('<?php echo $path; ?>', this.form, 'json', 'POST', true);" />
                                    <input type="button" value="Update URL" onclick="APIView.updateURL(this.form);" />
                                    <input id="btnCustomSubmit" type="button" value="Custom" onmousemove="jQuery('#spanCustom').fadeIn();" onclick="APIView.submit('<?php echo $path; ?>', this.form, '', jQuery('#txtCustomMethod').val(), false, jQuery('#txtCustomText').val());" />
                                    <span id="spanCustom" style="display: none">
                                        <label>Accepts:
                                            <input  id="txtCustomText" type="text" value="*/*" size="4" />
                                        </label>
                                        <label>Method:
                                            <input id="txtCustomMethod" type="text" value="GET" size="4" />
                                        </label>
                                    </span>
            <?php
            $Table->renderEnd();

        RI::ai(-1);
        echo RI::ni(), "</form>";
        RI::ni();
                    ?><div class="response-container" style="<?php if(!$Response) echo 'display: none'; ?>">
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
                    </div><?php
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
