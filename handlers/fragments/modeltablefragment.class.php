<?php
namespace CPath\Handlers\Fragments;

use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Helpers\Describable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Model\DB\PDOModel;

class ModelTableFragment implements IHandler{

    private $mModel, $mTheme;

    /**
     * @param PDOModel|Array $Model
     * @param ITableTheme $Theme
     */
    public function __construct($Model, ITableTheme $Theme = null) {
        $this->mModel = $Model;
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request)
    {
        $export = $this->mModel;
        $caption = null;
        if($export instanceof PDOModel) {
            $caption = Describable::get($export)->getTitle();
            $export = $export->exportData();
        }
        $Util = new TableThemeUtil($Request, $this->mTheme);
        $Util->renderKeyPairsTable($export, 'Column', 'Value', $caption);
    }
}
