<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Util;

use CPath\Framework\Render\Interfaces\IRender;
use CPath\Framework\Response\Interfaces\IResponse;

class ResponseRenderUtil implements IRender {
    private $mResponse;
TODO: need this?
    function __construct(IResponse $Response) {
        $this->mResponse = $Response;
    }

    public function getResponse() {
        return $this->mResponse;
    }
}