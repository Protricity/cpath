<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render;


use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\JSON\IRenderJSON;
use CPath\Framework\Render\Text\IRenderText;
use CPath\Framework\Render\XML\IRenderXML;

interface IRender extends IRenderHTML, IRenderText, IRenderXML, IRenderJSON {
//    /**
//     * Render this request
//     * @param IRequest $Request the IRequest instance for this render
//     * @return String|void always returns void
//     */
//    function render(IRequest $Request);
}

