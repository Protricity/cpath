<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/4/14
 * Time: 5:24 PM
 */
namespace CPath\Render\HTML;

use CPath\Data\Describable\IDescribable;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\WriteOnceHeaderRenderer;
use CPath\Request\IRequest;
use CPath\Response\ResponseRenderer;

class HTMLResponseBody extends HTMLContainer
{
	const DOCTYPE = '<!DOCTYPE html>';
	const TAB = '  ';
	const TAB_START = 0;

	private $mTitle = null;

	public function __construct($_content=null) {
		foreach(func_get_args() as $arg)
			if(is_array($arg))
				foreach($arg as $aArg)
					$this[] = $aArg;
			else
				$this[] = $arg;
	}

	public function setHeaderTitle($title) {
		$this->mTitle = $title;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	protected function renderHTMLContent(IRequest $Request) {
		foreach($this->getContent() as $Render)
			$Render->renderHTML($Request);
	}

	/**
	 * Render HTML header html
	 * @param \CPath\Request\IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Writer
	 * @return WriteOnceHeaderRenderer the writer inst used
	 */
	public function renderHTMLHeaders(IRequest $Request, IHeaderWriter $Writer=null) {
		$Writer = $Writer ?: new WriteOnceHeaderRenderer();

		$Renders = $this->getContent();
		$First = reset($Renders);

		$title = $this->mTitle;
		if (!$title && $First instanceof IDescribable)
			$title = $First->getDescription();

		if($title)
			echo RI::ni(), "<title>", $title, "</title>";

		$this->writeHeaders($Request, $Writer);

		return $Writer;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
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
			$Renderer = new ResponseRenderer($ex);
			if(!$body) {
				$Renderer->writeHeaders($Request, $Writer ?: new WriteOnceHeaderRenderer());

				RI::ai(-1);
				echo RI::ni(), '</head>';

				echo RI::ni(), '<body', $Attr, '>';
				RI::ai(1);
			}

			$Renderer = new ResponseRenderer($ex);
			$Renderer->renderHTML($Request, $Attr, $Parent);
		}

		RI::ai(-1);
		echo RI::ni(), '</body>';

		RI::ai(-1);
		echo RI::ni(), '</html>';
	}

}
