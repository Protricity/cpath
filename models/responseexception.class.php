<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Models;

use CPath\Interfaces\IResponse;
use CPath\Interfaces\TResponseHelper;

class ResponseException extends \Exception implements IResponse {
    use TResponseHelper;

    function getStatusCode() { return IResponse::STATUS_ERROR; }

    function &getData()
    {
        $ex = $this->getPrevious() ?: $this;
        $arr = array(
            '_debug_trace' => $ex->getTrace(),
        );
        return $arr;
    }
}