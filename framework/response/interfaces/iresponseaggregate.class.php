<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Interfaces;

interface IResponseAggregate {
    /**
     * @return \CPath\Framework\Response\Interfaces\IResponse
     */
    function createResponse();
}
