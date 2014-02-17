<?php
namespace CPath\Handlers\Fragments;

use CPath\Describable\Describable;
use CPath\Framework\Data\Map\Types\ArrayMap;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Framework\Render\Interfaces\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;

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
//            foreach($Model->table()->getColumns() as $name => $Column)
//                if(isset($data[$name]))
//                    $export[$Column->getComment()] = $data[$name];
        } else {
            $export = $Model;
        }
        $Util = new TableThemeUtil($Request, $this->mTheme);
        $Util->renderKeyPairsTable($export, 'Column', 'Value', $caption); // TODO: direct render
    }
}
