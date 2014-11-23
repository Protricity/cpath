<?php
namespace CPath\Framework\Render\Fragment\Table;

use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\Render\Fragment\Common\Table;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Templates\Themes\CPathDefaultTheme;

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
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
	 * @param \CPath\Framework\Render\Fragment\Table\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 * @return String|void always returns void
	 */
    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null)
    {
        foreach($this->mQuery as $data) {
            $MF = new \CPath\Framework\Render\Fragment\Table\ModelTableFragment($data, $this->mTheme);
            $MF->renderHTML($Request, $Parent);
        }
    }
}