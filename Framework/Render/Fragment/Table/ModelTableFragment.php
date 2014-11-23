<?php
namespace CPath\Framework\Render\Fragment\Table;

use CPath\Describable\Describable;
use CPath\Framework\Data\Map\Common\ArrayMap;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Theme\Util\TableThemeUtil;
use CPath\Request\IRequest;
use CPath\Templates\Themes\CPathDefaultTheme;

class ModelTableFragment implements IRenderHTML {

    private $mModel, $mTheme;

    /**
     * @param PDOModel $Model
     * @param \CPath\Render\HTML\Theme\ITableTheme $Theme
     */
    public function __construct(PDOModel $Model, \CPath\Render\HTML\Theme\ITableTheme $Theme = null) {
        $this->mModel = $Model;
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
