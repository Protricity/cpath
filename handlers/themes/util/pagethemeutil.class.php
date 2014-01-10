<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Util;

use CPath\Handlers\Themes\Interfaces\IPageTheme;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;


class PageThemeUtil {
    private $mTheme, $mRequest;

    public function __construct(IRequest $Request, IPageTheme $Theme) {
        $this->mRequest = $Request;
        $this->mTheme = $Theme;
    }


    /**
     * Render a page section using a string or callback to fill the contents
     * @param String|Null $className optional class name for this section
     * @param String|Callable $content
     */
    public function renderSection($className=null, $content=null) {
        $this->mTheme->renderSectionStart($this->mRequest, $className);
        echo RI::ni(), !is_string($content) && is_callable($content) ? call_user_func($content) : $content;
        $this->mTheme->renderSectionEnd($this->mRequest);
    }
}