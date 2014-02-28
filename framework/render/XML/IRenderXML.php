<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\XML;


use CPath\Framework\Request\Interfaces\IRequest;

interface IRenderXML {

    /**
     * Render request as xml
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @return void
     */
    function renderXML(IRequest $Request, $rootElementName='root');
}