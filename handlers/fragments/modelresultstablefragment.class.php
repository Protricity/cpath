<?php
namespace CPath\Handlers\Fragments;

use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Helpers\Describable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Model\DB\PDOColumn;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;

class ModelResultsTableFragment implements IHandler{

    private $mQuery, $mTheme;

    /**
     * @param PDOSelect $Query
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
        $Table = new TableThemeUtil($Request, $this->mTheme);
        $Table->renderHeaderStart();

        $row = $this->mQuery->fetch();
        if($row instanceof PDOModel)
            $row = $row->exportData();

        if($this->mQuery->hasDescriptor()) {
            $Descriptor = $this->mQuery->getDescriptor();
            foreach($row as $key=>$value)
                $Table->renderTD($Descriptor->getColumnDescriptor($key)->getTitle());
        } else {
            foreach($row as $key=>$value)
                $Table->renderTD($key);
        }

        while($row) {
            $Table->renderRowStart();
            foreach($row as $key=>$value)
                $Table->renderTD($value);
            $row = $this->mQuery->fetch();
            if($row instanceof PDOModel)
                $row = $row->exportData();
        }

        $Table->renderEnd();
    }
}
