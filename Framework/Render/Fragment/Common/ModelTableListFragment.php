<?php
namespace CPath\Framework\Render\Fragment\Common;

use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Theme\CPathDefaultTheme;
use CPath\Framework\Render\Theme\Interfaces\ITableTheme;
use CPath\Framework\Render\Fragment\Common\ModelTableFragment;

class ModelTableListFragment implements IRenderHTML {

    private $mQuery, $mTheme;

    /**
     * @param \CPath\Framework\PDO\Query\PDOSelect $Query
     * @param \CPath\Framework\Render\Theme\Interfaces\ITableTheme $Theme
     */
    public function __construct(PDOSelect $Query, ITableTheme $Theme = null) {
        $this->mQuery = $Query;
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        foreach($this->mQuery as $data) {
            $MF = new \CPath\Framework\Render\Fragment\Common\ModelTableFragment($data, $this->mTheme);
            $MF->render($Request);
        }
    }
}