<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Builders\RouteBuilder;
use CPath\Handlers\IAPIParam;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRoute;
use CPath\Log;
use CPath\Util;

class APIInfo implements IHandler, ILogListener {

    const Build_Ignore = true;
    private $mLog = array();

    public function __construct() {
        if(Base::isDebug())
            Log::addCallback($this);
    }

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    function render(IRoute $Route)
    {
        if(!$apiClass = $Route->getNextArg()) {
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
        $this->renderApi($API, $Route);
    }

    function renderAPI(IAPI $API, IRoute $Route) {

        $basePath = Base::getClassPublicPath($this);
        $route = $Route->getPrefix();
        list($method, $path) = explode(' ', $route, 2);
        foreach($API->getFields() as $name=>$Field)
            if($Field instanceof IAPIParam)
                $route .= '/:'.$name;
        $num = 1;



        ?><html>
    <head>
        <base href="<?php echo $basePath; ?>" />
        <title><?php echo $route; ?></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
        <script src="libs/apiinfo.js"></script>
        <script src="libs/vkbeautify.min.js"></script>
        <link rel="stylesheet" href="libs/apistyle.css" />
    </head>
    <body>
    <h1><?php echo $route."<br />"; ?></h1>
    <h3>Params:</h3>
    <form class="field-form" >
        <ul class='field-table'>
            <li class='field-header clearfix'>
                <div class='field-num'>#</div>
                <div class='field-name'>Name</div>
                <div class='field-description'>Description</div>
                <div class='field-input'>Test</div>
            </li>
            <?php foreach($API->getFields() as $name=>$Field) { ?>
                <li class='field-item clearfix'>
                    <div class='field-num'><?php echo $num++; ?>.</div>
                    <div class='field-name'><?php echo $name; ?></div>
                    <div class='field-description'><?php echo $Field->getDescription(); ?></div>
                    <div class='field-input'><input name='<?php echo $name; ?>' value='<?php if(isset($_GET[$name])) echo preg_replace('/[^\w _-]/', '', $_GET[$name]); ?>' /></div>
                </li>
            <?php } ?>
            <li class='field-footer clearfix'>
                <div>
                    <input type="button" value="Submit JSON" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, 'json', '<?php echo $method; ?>')" />
                    <input type="button" value="Submit XML" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, 'xml', '<?php echo $method; ?>')" />
                    <input type="button" value="Submit TEXT" onclick="APIInfo.submit('<?php echo $path; ?>', this.form, 'text', '<?php echo $method; ?>')" />
                </div>
            </li>
        </ul>
    </form>
    <h3>Response</h3>
    <div class='response-content' style="display: none"></div>
    <h3>Response Headers</h3>
    <div class='response-header' style="display: none"></div>

    <?php if(false && Base::isDebug()) { ?>
        <h3>Debug</h3>
        <table><?php
            /** @var ILogEntry $log */
            //foreach($this->mLog as $log)
            //    echo "<tr><td>",$log->getTag(),"</td><td style='white-space: pre'>{$log}</td></tr>";

            ?></table>
    <?php } ?>
    </body>
        </html><?php
    }
}
