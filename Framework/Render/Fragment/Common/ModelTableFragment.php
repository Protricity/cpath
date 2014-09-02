<?php
namespace CPath\Framework\Render\Fragment\Common;

use CPath\Describable\Describable;
use CPath\Framework\Data\Map\Common\ArrayMap;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Theme\CPathDefaultTheme;
use CPath\Framework\Render\Theme\Interfaces\ITableTheme;
use CPath\Framework\Render\Theme\Util\TableThemeUtil;

class ModelTableFragment implements IRenderHTML {

    private $mModel, $mTheme;

    /**
     * @param PDOModel $Model
     * @param \CPath\Framework\Render\Theme\Interfaces\ITableTheme $Theme
     */
    public function __construct(PDOModel $Model, ITableTheme $Theme = null) {
        $this->mModel = $Model;
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
