<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Builders\RouteBuilder;
use CPath\Config;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Api\Interfaces\IParam;
use CPath\Helpers\Describable;
use CPath\Interfaces\IHandler;
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

class APIInfo implements IHandler, ILogListener {

    const BUILD_IGNORE = true;
    private $mLog = array();

    public function __construct() {
        if(Config::$Debug)
            Log::addCallback($this);
    }

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    function render(IRequest $Request) {
        if(!$apiClass = $Request->getNextArg()) {
            print("No API Class passed to ".__CLASS__);
            return;
        }
        $API = new $apiClass();
        if($API instanceof IHandlerAggregate)
            $API = $API->getAggregateHandler();

        if(!($API instanceof IAPI)) {
            print($apiClass. " is not an instance of IAPI");
            return;
        }
        $routes = $API->getAllRoutes(new RouteBuilder());
        $this->renderApi($API, current($routes), $Request);
    }

    function renderAPI(IAPI $API, IRoute $Route, IRequest $Request, IResponse $Response=null) {

        if($Response == NULL && strcasecmp($Request->getMethod(), 'get') !== 0)
            $Response = $API->execute($Request);

        $basePath = Base::getClassPublicPath($this);

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
        ?><html>
    <head>
        <base href="<?php echo $basePath; ?>" />
        <title><?php echo $route; ?></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="libs/apiinfo.js"></script>
        <script src="libs/vkbeautify.min.js"></script>
        <link rel="stylesheet" href="libs/apistyle.css" />
    </head>
    <body>
        <?php if(!empty($_SERVER['HTTP_REFERER'])) { ?><h4><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">Go Back</a></h4><?php } echo "\n"; ?>
        <h1><?php echo $route."<br />"; ?></h1>
        <h2><?php echo Describable::get($API)->getDescription();  ?></h2>
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
            </ul>
            <div>
                <input type="button" value="Submit JSON" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, 'json', '<?php echo $method; ?>');" />
                <input type="button" value="Submit XML" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, 'xml', '<?php echo $method; ?>');" />
                <input type="button" value="Submit TEXT" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, 'text', '<?php echo $method; ?>');" />
                <input type="submit" value="Submit POST"/>
                <input type="button" value="Submit JSON Object (POST)" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, 'json', 'POST', true);" />
                <span id="spanCustom" style="display: none">
                    <label>Accepts:
                        <input  id="txtCustomText" type="text" value="*/*" size="4" />
                    </label>
                    <label>Method:
                        <input id="txtCustomMethod" type="text" value="GET" size="4" />
                    </label>
                </span>
                <input id="btnCustomSubmit" type="button" value="Submit Custom" onmousemove="jQuery('#spanCustom').fadeIn();" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, '', jQuery('#txtCustomMethod').val(), false, jQuery('#txtCustomText').val());" />
                <input type="button" value="Update URL" onclick="APIInfo.updateURL(this.form);" />
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
    <?php } echo "\n"; ?>
    </body>
</html><?php
    }
}
