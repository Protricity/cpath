<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/31/2014
 * Time: 8:16 PM
 */
namespace CPath\Render\Text;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\Map\AbstractMapRenderer;
use CPath\Request\IRequest;


class TextMapRenderer extends AbstractMapRenderer
{
	private static $mStarted = false;

	public function __construct(IRequest $Request, $Map) {
		parent::__construct($Request, $Map);
	}

	protected function renderNamedValue($name, $value) {
		if (self::$mStarted)
			echo RI::ni();
		self::$mStarted = true;
		echo $name, ": ";
		$ret = parent::renderNamedValue($name, $value);

		return $ret;
	}

	protected function renderValue($value) {
		if (self::$mStarted)
			echo RI::ni();
		self::$mStarted = true;
		$ret            = parent::renderValue($value);

		return $ret;
	}


	protected function renderStart($isArray) {
	}

	protected function renderEnd($isArray) {
	}
}