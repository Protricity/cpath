<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/1/14
 * Time: 10:04 PM
 */
namespace CPath\Framework\Render\Fragment\Common;

use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;

class HTMLFragment implements IRenderHTML
{

    private $mHTML;

    public function __construct($html)
    {
        $this->mHTML = $html;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        echo RI::ni(), $this->mHTML;
    }
}