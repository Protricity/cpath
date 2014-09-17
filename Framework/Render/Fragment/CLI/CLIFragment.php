<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/14
 * Time: 1:22 PM
 */
namespace CPath\Framework\Render\Fragment\CLI;

use CPath\Base;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Framework\Render\Util\RenderIndents as RI;

class CLIFragment implements IRenderHTML, ISupportHeaders
{

    public function __construct() {
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $Head->writeScript(__NAMESPACE__ . '\assets\cli.js');
        $Head->writeStyleSheet(__NAMESPACE__ . '\assets\cli.css');
    }

    /**
     * Render request as html
     * @param \CPath\Framework\Render\Fragment\CLI\IRenderRequest|\CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRenderRequest $Request, IAttributes $Attr = null)
    {
        echo RI::ni(), "<div class='cli-fragment'>";
        echo "CLI";
        echo RI::ni(), "</div>";
    }
}