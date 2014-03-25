<?php
namespace CPath\Handlers\Fragments;

use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;

class ModelTableListFragment implements IRender {

    private $mQuery, $mTheme;

    /**
     * @param \CPath\Framework\PDO\Query\PDOSelect $Query
     * @param ITableTheme $Theme
     */
    public function __construct(PDOSelect $Query, ITableTheme $Theme = null) {
        $this->mQuery = $Query;
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request)
    {
        foreach($this->mQuery as $data) {
            $MF = new ModelTableFragment($data, $this->mTheme);
            $MF->render($Request);
        }
    }
}
