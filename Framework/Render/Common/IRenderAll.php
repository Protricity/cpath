<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\Common;

use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\JSON\IRenderJSON;
use CPath\Framework\Render\Text\IRenderText;
use CPath\Framework\Render\XML\IRenderXML;

interface IRenderAll extends IRender, IRenderHTML, IRenderText, IRenderXML, IRenderJSON {
}