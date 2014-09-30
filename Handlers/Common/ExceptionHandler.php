<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/5/14
 * Time: 4:20 PM
 */
namespace CPath\Handlers\Common;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;
use CPath\Handlers\HTML\AbstractHTMLHandler;

class ExceptionHandler extends AbstractHTMLHandler {

    private $mEx;
    public function __construct(\Exception $Ex, IHTMLContainer $Template) {
        parent::__construct($Template);
        $this->mEx = $Ex;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        echo RI::ni(), $this->mEx;
    }
}