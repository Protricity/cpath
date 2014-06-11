<?php
namespace CPath\Framework\View\Templates\Fragments;

use CPath\Describable\Describable;
use CPath\Framework\Data\Map\Common\ArrayMap;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\Theme\CPathDefaultTheme;
use CPath\Framework\View\Theme\Interfaces\ITableTheme;
use CPath\Framework\View\Theme\Util\TableThemeUtil;

class ModelTableFragment implements IRender{

    private $mModel, $mTheme;

    /**
     * @param PDOModel $Model
     * @param ITableTheme $Theme
     */
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
        $caption = null;
        if($Model instanceof PDOModel) {
            $caption = Describable::get($Model)->getTitle();
            $export = ArrayMap::get($Model);

//            //  TODO: why?
//            foreach($Model->table()->getColumns() as $Column)
//                if(isset($data[$name]))
//                    $export[$Column->getComment()] = $data[$name];
        } else {
            $export = $Model;
        }
        $Util = new TableThemeUtil($Request, $this->mTheme);
        $Util->renderKeyPairsTable($export, 'Column', 'Value', $caption); // TODO: direct render
    }
}
