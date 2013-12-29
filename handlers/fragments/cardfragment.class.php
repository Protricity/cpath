<?php
namespace CPath\Handlers\Fragments;

use CPath\Describable\IDescribable;
use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\IFragmentTheme;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;

abstract class AbstractCardFragment implements IHandler{

    /** @var IFragmentTheme */
    private $mTheme;

    /**
     * @param IDescribable|mixed $Description
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
