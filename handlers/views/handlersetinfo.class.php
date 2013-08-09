<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Builders\RouteBuilder;
use CPath\Handlers\InvalidRouteException;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IRoute;
use CPath\Interfaces\InvalidHandlerException;
use CPath\Log;
use CPath\Model\Route;
use CPath\Util;

class HandlerSetInfo implements IHandler, ILogListener {

    const Build_Ignore = true;
    private $mLog = array();

    public function __construct() {
        if(Base::isDebug())
            Log::addCallback($this);
    }

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    function render(IRequest $Request)
    {
        $HandlerRoute = $Request->getRoute();
        $handlerRoute = $HandlerRoute->getPrefix();
        if(!$apiClass = $Request->getNextArg())
            die("No API Class passed to ".__CLASS__);
        $Source = new $apiClass;
        if($Source instanceof IHandlerAggregate) {
            $Handlers = $Source->getAggregateHandler();
        } else {
            print($apiClass. " does not implement IHandlerAggregate");
            return;
        }
        if(!($Handlers instanceof IHandlerSet)) {
            print(get_class($Handlers). " is not an instance of IHandlerSet");
            return;
        }

        $routes = $Handlers->getAllRoutes(new RouteBuilder());
        $handlers = array();
        $ids = array();
        $num = 1;
        foreach($Handlers as $route=>$Handler) {
            $handlers[$route] = $Handler;
            $ids[$route] = $num++;
        }

        if($arg = $Request->getNextArg()) {
            $route = array_search($arg, $ids);
            $API = $Handlers->get($route);
            if(!$API instanceof IAPI)
                throw new InvalidRouteException("Destination for '{$arg}' does not implement IAPI");
            $APIInfo = new APIInfo();
            $APIRoute = $routes[$route];
            $APIInfo->renderAPI($API, $APIRoute);
            return;
        }

        $basePath = Base::getClassPublicPath($this);
        list(,$infoPath) = explode(' ', $HandlerRoute->getPrefix(), 2);
        $infoPath = substr(Base::getDomainPath(), 0, -1) . $infoPath .'/';

        $num = 1;
?><html>
    <head>
        <base href="<?php echo $basePath; ?>" />
        <title><?php echo $handlerRoute; ?></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
        <link rel="stylesheet" href="<?php echo $basePath; ?>libs/apistyle.css" />
    </head>
    <body>
        <h1><?php echo $handlerRoute."<br />"; ?></h1>

        <ul class='field-table'>
            <li class='field-header clearfix'>
                <div class='field-num'>#</div>
                <div class='field-prefix'>Route</div>
                <div class='field-description'>Description</div>
                <div class='field-destination'>Destination</div>
            </li>
            <?php if(false) { ?>
                <li class='field-item clearfix'>
                    <div class='field-num'></div>
                    <div class='field-prefix'></div>
                    <div class='field-description'>No Description</div>
                    <div class='field-destination'>No Routes available in this IHandlerSet</div>
                </li>
            <?php } else foreach($handlers as $route => $Handler) {
                $description = "No Description";
                try{
                    if($Handler instanceof IDescribable)
                        $description = $Handler->getDescription();
                } catch (InvalidRouteException $ex) {}
                ?>
                <li class='field-item clearfix'>
                    <div class='field-num'><?php echo $num++; ?>.</div>
                    <div class='field-prefix'><a href='<?php echo $infoPath . $ids[$route]. '#' . $route; ?>'><?php echo $routes[$route]->getPrefix(); ?></a></div>
                    <div class='field-description'><?php echo $description; ?></div>
                    <div class='field-destination'><?php echo $routes[$route]->getDestination(); ?></div>
                </li>
            <?php } ?>
                <li class='field-footer clearfix'>
                <div class='field-num'></div>
                <div class='field-prefix'></div>
                <div class='field-description'></div>
                <div class='field-destination'></div>
            </li>
        </ul>
    </body>
</html><?php
    }
}
