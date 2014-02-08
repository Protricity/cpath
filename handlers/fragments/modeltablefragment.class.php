<?php
namespace CPath\Handlers\Fragments;

use CPath\Describable\Describable;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Interfaces\IHandler;
use CPath\Framework\Request\Interfaces\IRequest;

class ModelTableFragment implements IHandler{

    private $mModel, $mTemplate, $mTheme;

    /**
     * @param \CPath\Framework\PDO\Model\PDOModel|Array $Model
     * @param \CPath\Framework\PDO\Model\PDOModel $Template a PDOModel instance to use as a template
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
        $Model = $this->mModel;
        $export = array();
        $caption = null;
        if($Model instanceof PDOModel) {
            $caption = Describable::get($this->mTemplate)->getTitle();
            $data = $Model->exportData();
            foreach($Model->table()->getColumns() as $name => $Column)
                if(isset($data[$name]))
                    $export[$Column->getComment()] = $data[$name];
        } else {
            $export = $Model;
        }
        $Util = new TableThemeUtil($Request, $this->mTheme);
        $Util->renderKeyPairsTable($export, 'Column', 'Value', $caption);
    }
}
