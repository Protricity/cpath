<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\XML;


use CPath\Request\IRequest;

interface IRenderXML {

    /**
     * Render request as xml
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @param bool $declaration if true, the <!xml...> declaration will be rendered
     * @return String|void always returns void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false);
}