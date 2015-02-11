<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/31/2014
 * Time: 5:14 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\Map\AbstractMapRenderer;
use CPath\Request\IRequest;

class HTMLMapRenderer extends AbstractMapRenderer
{
	public function __construct(IRequest $Request, $Map) {
		parent::__construct($Request, $Map);
	}

	protected function renderNamedValue($name, $value) {
		echo RI::ni(), "<dt>", $name, "</dt>";
		echo RI::ni(), "<dd>";
		if(is_scalar($value)) {
			HTMLConfig::renderNamedValue($name, $value);
			$ret = true;

		} else if ($value instanceof IRenderHTML) {
			RI::ai(1);
			$value->renderHTML($this->getRequest());
			RI::ai(-1);
			$ret = true;
			echo RI::ni();

		} else {
			$ret = parent::renderNamedValue($name, $value);
		}
		echo "</dd>";

		return $ret;
	}

	protected function renderValue($value) {
		echo RI::ni(), "<li>";
		RI::ai(1);

		if(is_scalar($value)) {
			HTMLConfig::renderValue($value);
			$ret = true;

		} else if ($value instanceof IRenderHTML) {
			$value->renderHTML($this->getRequest());
			$ret = true;

		} else {
			$ret = parent::renderValue($value);
		}
		RI::ai(-1);

		echo RI::ni(), "</li>";

		return $ret;
	}


	protected function renderStart($isArray) {
		if ($isArray === true) {
			echo RI::ni(), "<ul>";
			RI::ai(1);

		} else if ($isArray === false) {
			echo RI::ni(), "<dl>";
			RI::ai(1);
		}
	}

	protected function renderEnd($isArray) {
		if ($isArray === true) {
			RI::ai(-1);
			echo RI::ni(), "</ul>";

		} else if ($isArray === false) {
			RI::ai(-1);
			echo RI::ni(), "</dl>";
		}
	}

	// Static


	/** @var IHTMLValueRenderer */
	static $mValueRenderers = array() ;
	public function addValueRenderer(IHTMLValueRenderer $Renderer) {
		$this->mValueRenderers = $Renderer;
	}
}