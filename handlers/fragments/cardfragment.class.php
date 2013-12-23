<?php
namespace CPath\Handlers\Fragments;

use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\IFragmentTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Helpers\Describable;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;

abstract class AbstractCardFragment implements IHandler{

    private $mObject;
    /** @var IFragmentTheme */
    private $mTheme;

    /**
     * @param IDescribable|mixed $Object
     * @param IFragmentTheme $Theme
     */
    public function __construct(IDescribable $Description, IFragmentTheme $Theme = null) {
        $this->mDesc = $Description;
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render the card content
     * @param IRequest $Request
     * @param IDescribable $Description
     * @return mixed
     */
    abstract function renderCardContent(IRequest $Request, IDescribable $Description);

    final function addButton($text) {

    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request)
    {
        $Desc = $this->mDesc;
        $caption = null;
        $Theme = $this->mTheme;
        $Theme->renderFragmentStart($Request, $Desc, 'card');
        $this->renderCardContent($Request, $Desc);
        $Theme->renderFragmentEnd($Request);
    }
}
