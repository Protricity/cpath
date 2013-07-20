<?php
namespace CPath\Handlers\Api\View;

use CPath\Util;
use CPath\Handlers\Api;
use CPath\Interfaces\IResponse;

class ApiInfo {

    function render(Api $Api, IResponse $Response)
    {

        $route = $Api->getDisplayRoute($methods);
?><html>
    <head>
        <title><?php echo $route; ?></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    </head>
    <body>
        <h1><?php echo $route."<br />"; ?></h1>
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
