<?php
namespace CPath\Handlers\Api\View;

use CPath\Util;
use CPath\Handlers\Api;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IResponse;

class ApiInfo {

    function render(Api $Api, IResponse $Response)
    {
        $routes = $Api->getDisplayRoutes();
?><html>
    <head>
        <title><?php echo $routes[0]; ?></title>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    </head>
    <body>
        <h1><?php foreach($routes as $route) echo $route."<br />"; ?></h1>
        <h3>Params:</h3>
        <table>
        <?php foreach($Api->getFields() as $name=>$Field) { ?>
            <tr><td><?php echo $name; ?></td><td><?php echo $Field->getDescription(); ?></td>
        <?php } ?>
        </table>
        <h3>Response</h3>
        <div style='white-space: pre'><?php
            echo $Response;
        ?></div>
        <h3>JSON Response</h3>
        <div style='white-space: pre'><?php
             echo htmlentities(json_encode(Util::toJSON($Response)));
        ?></div>
        <h3>XML Response</h3>
        <div style='white-space: pre'><?php
            $dom = dom_import_simplexml(Util::toXML($Response))->ownerDocument;
            $dom->formatOutput = true;
            echo htmlentities($dom->saveXML());

        ?></div>
    </body>
</html><?php
    }
}
