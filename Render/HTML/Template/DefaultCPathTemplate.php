<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/20/14
 * Time: 11:23 PM
 */
namespace CPath\Render\HTML\Template;

use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Render\HTML\Common\HTMLText;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Header\HeaderConfig;
use CPath\Render\HTML\Header\HTMLMetaTag;
use CPath\Render\HTML\HTMLConfig;
use CPath\Render\HTML\HTMLContainer;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\HTMLResponseBody;
use CPath\Render\HTML\IHTMLValueRenderer;
use CPath\Render\IRenderAll;
use CPath\Render\Map\MapRenderer;
use CPath\Request\Executable\IExecutable;
use CPath\Request\IRequest;
use CPath\Request\Session\ISessionRequest;
use CPath\Response\IResponse;
use CPath\Response\IResponseHeaders;
use CPath\Response\ResponseRenderer;
use CPath\Route\CPathMap;
use CPath\Route\HTML\HTMLRouteNavigator;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;
use CPath\Route\RouteIndex;
use CPath\Route\RouteRenderer;

class DefaultCPathTemplate extends HTMLContainer implements IRoutable, IBuildable {

	const META_PATH = 'path';
	const META_DOMAIN_PATH = 'domain-path';

	public function __construct($_content=null) {
		$Render = new HTMLResponseBody(
            new HTMLElement('section', 'header',
                new HTMLElement('h1', 'header-title')
			),
			$Content = new HTMLElement('section', 'content',
                new HTMLElement('div', 'navbar'
                )
			),
			$Footer = new HTMLElement('section', 'footer'
			)
		);

		parent::__construct($Render);
        $this->setContainer($Content);

        $Render->addSupportHeaders($this);
		$this->addHeaderScript(HeaderConfig::$JQueryPath);
		$this->addHeaderScript(__DIR__ . '/assets/default-template.js');
		$this->addHeaderStyleSheet(__DIR__ . '/assets/default-template.css');

		$this->addAll(func_get_args());
	}

	// Static

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request inst for this build session
	 * @return void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleBuildStatic(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '_default_template');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}

	/**
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest inst for this render
	 * @param Object[]|null $Previous all previous response object that were passed from a handler, if any
	 * @param RouteRenderer|null $RouteRenderer
	 * @param array $args
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
	static function routeRequestStatic(IRequest $Request, Array &$Previous = array(), $RouteRenderer=null, $args=array()) {
        if(!$Request->getMimeType() instanceof HTMLMimeType) {
            $Object = $Previous[0];
            if($Object instanceof IExecutable)
                $Object = $Object->execute($Request);

            if(!$Object instanceof IRenderAll) {
                if($Object instanceof IKeyMap || $Object instanceof ISequenceMap)
                    $Object = new MapRenderer($Object);
                elseif($Object instanceof IResponse)
                    $Object = new ResponseRenderer($Object);
            }

            if($Object instanceof IRenderAll) {
                $Object->render($Request, true);
                return true;
            }

            echo "Could not render: ", get_class($Object);
            return true;
        }

        static $CustomLoader = null;
        $CustomLoader ?: HTMLConfig::addValueRenderer($CustomLoader = new CustomHTMLValueRenderer($Request));

		$Template = new DefaultCPathTemplate();
        $Template->matchContainer('.navbar')
            ->addContent(new HTMLElement('h3', 'navbar-title', $Request->getPath()));

		$Object = reset($Previous);
		if($RouteRenderer instanceof RouteRenderer && $Request instanceof ISessionRequest) {
			if(!$Object)
				$Object = new RouteIndex($Request, $RouteRenderer);
            $Navigator = new HTMLRouteNavigator($RouteRenderer);
//            $Navigator->addClass($Request->getSessionID() ? IRequest::NAVIGATION_NO_SESSION_CLASS : IRequest::NAVIGATION_SESSION_ONLY_CLASS);
            $Template
                ->matchContainer('.navbar')
                ->addContent($Navigator);
		}

		if ($Object instanceof IResponseHeaders) {
			$Object->sendHeaders($Request);

		} else if ($Object instanceof IResponse) {
			$ResponseRenderer = new ResponseRenderer($Object);
			$ResponseRenderer->sendHeaders($Request);
		}

        if(!headers_sent()) {
            header('Cache-Control: private, max-age=0, no-cache, must-revalidate, no-store, proxy-revalidate');
            if (isset($_SERVER['REQUEST_URI']))
                header('X-Location: ' . $_SERVER['REQUEST_URI']);
        }

        $Template
            ->matchContainer('.header')
            ->addContent(new HTMLText($Request->getMethodName() . ' ' . $Request->getPath()));

		$Template->addMetaTag(HTMLMetaTag::META_CONTENT_TYPE, 'text/html; charset=utf-8');
		$Template->addMetaTag(self::META_PATH, $Request->getPath());
		$Template->addMetaTag(self::META_DOMAIN_PATH, $Request->getDomainPath(false));

		for($i=0; $i<sizeof($Previous); $i++)
			$Template->addAll($Previous[$i]);

		$Template->renderHTML($Request);
		return true;
	}

}

class CustomHTMLValueRenderer implements IHTMLValueRenderer {
    private $Request;

    function __construct(IRequest $Request) {
        $this->Request = $Request;
    }


    /**
     * @param $key
     * @param $value
     * @param null $arg1
     * @return bool if true, the value has been rendered, otherwise false
     */
    function renderNamedValue($key, $value, $arg1=null) {
        switch($key) {
            case 'trace':
                $value = nl2br($value);
                echo $value;
                return true;

            case 'description':
                if(strlen($value) > 32)
                    $value = substr($value, 0, 29) . '...';
                echo $value;
                return true;

            case 'url':
                $href = $value;
                $domain = $this->Request->getDomainPath();
                if(strpos($href, $domain) !== 0)
                    $href = rtrim($domain, '/') . '/' . ltrim($href, '/');
                echo "<a href='{$href}'>", $arg1 ?: '[link]', "</a>";
                return true;
        }
        return false;
    }

    /**
     * @param $value
     * @return bool if true, the value has been rendered, otherwise false
     */
    function renderValue($value) {
        return false;
    }

}
