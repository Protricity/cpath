<?php
namespace CPath\Framework\Render\Fragment\Table;

use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Fragment\Common\Table;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IStaticRequestHandler;
use CPath\Request\IRequest;
use CPath\Templates\Themes\CPathDefaultTheme;
use CPath\Render\HTML\Theme\ITableTheme;
use CPath\Framework\Render\Fragment\Table\ModelTableFragment;

class ModelTableListFragment implements IRenderHTML {

    private $mQuery, $mTheme;

    /**
     * @param \CPath\Framework\PDO\Query\PDOSelect $Query
     * @param \CPath\Render\HTML\Theme\ITableTheme $Theme
     */
    public function __construct(PDOSelect $Query, \CPath\Render\HTML\Theme\ITableTheme $Theme = null) {
        $this->mQuery = $Query;
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render request as html
     * @param \CPath\Framework\Render\Fragment\Table\IRenderRequest|\CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRenderRequest $Request, IAttributes $Attr = null)
    {
        foreach($this->mQuery as $data) {
            $MF = new \CPath\Framework\Render\Fragment\Table\ModelTableFragment($data, $this->mTheme);
            $MF->render($Request);
        }
    }
}