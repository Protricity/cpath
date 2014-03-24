<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/24/14
 * Time: 11:47 AM
 */
namespace CPath\Framework\Render;

use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\JSON\IRenderJSON;
use CPath\Framework\Render\Text\IRenderText;
use CPath\Framework\Render\XML\IRenderXML;
use CPath\Framework\Request\Interfaces\IRequest;

interface IRenderAll extends IRenderHTML, IRenderText, IRenderXML, IRenderJSON
{
}
