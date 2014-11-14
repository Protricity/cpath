<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/4/14
 * Time: 5:24 PM
 */
namespace CPath\Render\HTML;

use CPath\Describable\IDescribable;
use CPath\Framework\Render\Header\WriteOnceHeaderRenderer;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Handlers\RenderHandler;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;

class HTMLResponseBody extends HTMLContainer
{
	const DOCTYPE = '<!DOCTYPE html>';
	const TAB = '  ';
	const TAB_START = 0;

	/** @var IHTMLSupportHeaders[] */
	private $mHeaders = array();

	public function addHeaders(IHTMLSupportHeaders $Headers) {
		$this->mHeaders[] = $Headers;
	}

	public function __construct($_content=null) {
		foreach(func_get_args() as $arg)
			if(is_array($arg))
				foreach($arg as $aArg)
					$this[] = $aArg;
			else
				$this[] = $arg;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	protected function renderHTMLContent(IRequest $Request) {
		foreach($this->getContent() as $Render)
			$Render->renderHTML($Request);
	}

	/**
	 * Render HTML header html
	 * @param \CPath\Request\IRequest $Request
	 * @return WriteOnceHeaderRenderer the writer instance used
	 */
	public function renderHTMLHeaders(IRequest $Request) {
		$Writer = new WriteOnceHeaderRenderer();

		$Renders = $this->getContent();
		$First = reset($Renders);

		if ($First instanceof IDescribable) {
			$title = $First->getTitle();
			echo RI::ni(), "<title>", $title, "</title>";
		}

		if ($this instanceof IHTMLSupportHeaders)
			$this->writeHeaders($Request, $Writer);

		foreach($this->mHeaders as $Headers)
			$Headers->writeHeaders($Request, $Writer);

		foreach($Renders as $Render)
			if ($Render instanceof IHTMLSupportHeaders)
				$Render->writeHeaders($Request, $Writer);

		return $Writer;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		RI::si(static::TAB_START, static::TAB);
		echo self::DOCTYPE;

		echo RI::ni(), '<html>';
		RI::ai(1);

		echo RI::ni(), '<head>';
		RI::ai(1);
		$body = false;
		$Writer = null;
		try {

			$Writer = $this->renderHTMLHeaders($Request);

			RI::ai(-1);
			echo RI::ni(), '</head>';

			echo RI::ni(), '<body', $Attr, '>';
			RI::ai(1);

			$body = true;
			$this->renderHTMLContent($Request);

		} catch (\Exception $ex) {
			if(!$body) {
				RI::ai(-1);
				echo RI::ni(), '</head>';

				echo RI::ni(), '<body', $Attr, '>';
				RI::ai(1);
			}

			if(!$Writer)
				$Writer = new WriteOnceHeaderRenderer();

			$Renderer = new RenderHandler($ex);
			$Renderer->writeHeaders($Request, $Writer);
			$Renderer->renderHTML($Request);
		}

		RI::ai(-1);
		echo RI::ni(), '</body>';

		RI::ai(-1);
		echo RI::ni(), '</html>';
	}

}
