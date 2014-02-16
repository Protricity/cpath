<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Util;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Fragments\IRenderFragmentContent;
use CPath\Handlers\Themes\Interfaces\IFragmentTheme;
use CPath\Misc\RenderIndents as RI;


class FragmentThemeUtil {
    private $mTheme, $mRequest;

    public function __construct(IRequest $Request, IFragmentTheme $Theme) {
        $this->mRequest = $Request;
        $this->mTheme = $Theme;
    }

    /**
     * @param String|IDescribable $Object
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     */
    public function renderFragment($Object, $class=null, $attr=null) {
        $Desc = Describable::get($Object);

        $this->mTheme->renderFragmentStart($this->mRequest, $Desc->getTitle(), $class, $attr);

        if($Object instanceof IRenderFragmentContent) {
            $Object->renderFragmentContent($this->mRequest);
        } else {
            $this->renderContent($Object->getDescription());
        }

        $this->mTheme->renderFragmentEnd($this->mRequest);
    }

    private function renderContent ($content) {
        echo RI::ni(), !is_string($content) && is_callable($content) ? call_user_func($content) : ($content === null ? 'null' : $content);
    }
}