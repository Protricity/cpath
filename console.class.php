<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Interfaces\IBuilder;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRoute;
use CPath\Model\Response;
use CPath\Handlers\Api;

class Console implements IHandler {

    const ROUTE_PATH = '/console';     // Allow manual building from command line: 'php index.php build'
    const ROUTE_METHODS = 'CLI';    // CLI only

    function render(IRoute $Route)
    {
        while(true) {
            echo ">";
            $cmd=trim(fgets(STDIN));
            echo "Command: $cmd\n";

            if($cmd == 'exit')
                break;
        }
    }
}