<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\View\Theme\Util;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\Templates\Fragments\IRenderFragmentContent;
use CPath\Framework\View\Theme\Interfaces\IFragmentTheme;


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