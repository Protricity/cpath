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

    public function __construct(PDOModel $Model, ITableTheme $Theme = null) {
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
        $Model = $this->mModel;
        $Util = new TableThemeUtil($Request, $this->mTheme);
        $Util->renderKeyPairsTable($Model->exportData(), 'Column', 'Value', Describable::get($Model)->getTitle());
    }
}
