<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/14
 * Time: 1:22 PM
 */
namespace CPath\Framework\Render\Fragment\CLI;

use CPath\Base;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class CLIFragment implements IRenderHTML, IHTMLSupportHeaders
{

    public function __construct() {
    }

    /**
     * Write all support headers used by this IView inst
     * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer inst to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $Head->writeScript(__NAMESPACE__ . '\assets\cli.js');
        $Head->writeStyleSheet(__NAMESPACE__ . '\assets\cli.css');
    }

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
	 * @param \CPath\Framework\Render\Fragment\CLI\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 * @return String|void always returns void
	 */
    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null)
    {
        echo RI::ni(), "<div class='cli-fragment'>";
        echo "CLI";
        echo RI::ni(), "</div>";
    }
}