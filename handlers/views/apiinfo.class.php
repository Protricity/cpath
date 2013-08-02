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
        foreach($API->getFields() as $name=>$Field)
            if($Field instanceof IAPIParam)
                $route .= '/:'.$name;
?><html>
    <head>
        <base href="<?php echo $basePath; ?>" />
        <title><?php echo $route; ?></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
        <link rel="stylesheet" href="libs/apistyle.css" />
    </head>
    <body>
        <h1><?php echo $route."<br />"; ?></h1>
        <h3>Params:</h3>
        <ul class='param-table'>
            <li class='param-header clearfix'>
                <div class='param-name'>Name</div>
                <div class='param-description'>Description</div>
            </li>
        <?php foreach($API->getFields() as $name=>$Field) { ?>
            <li class='param-item clearfix'>
                <div class='param-name'><?php echo $name; ?></div>
                <div class='param-description'><?php echo $Field->getDescription(); ?></div>
            </li>
        <?php } ?>
        </ul>
        <h3>Response</h3>
        <div class='response-content'></div>

        <?php if(Base::isDebug()) { ?>
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
