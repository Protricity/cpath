<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/2/14
 * Time: 10:57 PM
 */
namespace CPath\Render;


use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\Text\IRenderText;
use CPath\Render\XML\IRenderXML;
use CPath\Request\IRequest;

interface IRenderAll extends IRenderHTML, IRenderText, IRenderJSON, IRenderXML {

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest inst for this render
	 * @param bool $sendHeaders if true, sends the response headers
	 * @return bool returns false if no rendering occurred
	 */
    function render(IRequest $Request, $sendHeaders=true);
}

//
//interface IRender
//{
//    /**
//     * Renders a response object or returns false
//     * @param IRequest $Request the IRequest inst for this render
//     * @return bool returns false if no rendering occurred
//     */
//    function render(IRequest $Request);
//}