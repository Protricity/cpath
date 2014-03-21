<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\Util;

use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Route\Render\IDestination;
use CPath\Framework\Render\JSON\IRenderJSON;
use CPath\Framework\Render\Text\IRenderText;
use CPath\Framework\Render\XML\IRenderXML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Route\Map\Common\CallbackRouteMap;
use CPath\Framework\Route\Routable\IRoutable;

class MissingRenderModeException extends \Exception {}

class RenderUtil implements IDestination {
    private $mTarget;

    function __construct($Target) {
        $this->mTarget = $Target;
    }

    public function getTarget() {
        return $this->mTarget;
    }

    /**
     * Render this API Call. The output format is based on the requested mimeType from the browser
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @throws MissingRenderModeException
     * @return void
     */
    function renderDestination(IRequest $Request) {
        $Target = $this->mTarget;
        if($Target instanceof IRoutable) {
            $Target->mapRoutes(new CallbackRouteMap($Target, function($prefix, IDestination $Destination) use ($Request, &$Target) {
                list($method, $path) = explode(' ', $prefix, 2);
                if($Request->getMethod() === $method || $method === 'ANY') {
                    if($Request->getPath() === $path) {
                        $Target = $Destination;
                    }
                }
            }));
        }

        foreach($Request->getMimeTypes() as $mimeType) {
            switch($mimeType) {
                case 'application/json':
                    if($Target instanceof IRenderJSON) {
                        $Target->renderJSON($Request);
                        return;
                    }
                    break;

                case 'application/xml':
                    if($Target instanceof IRenderXML) {
                        $Target->renderXML($Request);
                        return;
                    }
                    break;

                case 'text/html':
                    if($Target instanceof IRenderHTML) {
                        $Target->renderHTML($Request);
                        return;
                    }
                    break;

                case 'text/plain':
                    if($Target instanceof IRenderText) {
                        $Target->renderText($Request);
                        return;
                    }
                    break;
            }
        }

//        if($T instanceof IRender) { // NO NO !
//            $T->render($Request);
//            return;
//        }

        if($Target instanceof IRenderText) {
            $Target->renderText($Request);
            return;
        }

        throw new MissingRenderModeException("Render mode could not be determined for: " . implode(', ', $Request->getMimeTypes()));
    }

}