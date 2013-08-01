<?php
namespace CPath\Handlers\Views;

use CPath\Base;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IHandler;
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
        if(!$apiClass = $Route->getNextArg())
            die("No API Class passed to ".__CLASS__);
        $R = new \ReflectionClass($apiClass);
        if($R->implementsInterface("CPath\\Interfaces\\IHandlerAggregate")) {
            $API = $apiClass::getHandler();
        } else {
            $API = new $apiClass();
        }
        if(!($API instanceof IAPI))
            die($apiClass. " is not an instance of IAPI");
        $this->renderApi($API);
    }

    function renderAPI(IAPI $API) {
        $route = ';'; //$API->getDisplayRoute($methods);
?><html>
    <head>
        <title><?php echo $route; ?></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    </head>
    <body>
        <h1><?php echo $route."<br />"; ?></h1>
        <h3>Params:</h3>
        <table>
        <?php foreach($API->getFields() as $name=>$Field) { ?>
            <tr><td><?php echo $name; ?></td><td><?php echo $Field->getDescription(); ?></td>
        <?php } ?>
        </table>
        <h3>Response</h3>
        <div style='white-space: pre'><?php
            //echo $Response;
        ?></div>
        <h3>JSON Response</h3>
        <div style='white-space: pre'><?php
             //echo htmlentities(json_encode(Util::toJSON($Response)));
        ?></div>
        <h3>XML Response</h3>
        <div style='white-space: pre'><?php
            //$dom = dom_import_simplexml(Util::toXML($Response))->ownerDocument;
            //$dom->formatOutput = true;
            //echo htmlentities($dom->saveXML());

        ?></div>

        <?php if(Base::isDebug()) { ?>
        <h3>Debug</h3>
        <table><?php
            /** @var ILogEntry $log */
            foreach($this->mLog as $log)
                echo "<tr><td>",$log->getTag(),"</td><td style='white-space: pre'>{$log}</td></tr>";

        ?></table>
        <?php } ?>
    </body>
</html><?php
    }
}
