<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/31/2014
 * Time: 8:15 PM
 */
namespace CPath\Render\XML;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\Map\AbstractMapRenderer;
use CPath\Request\IRequest;


class XMLMapRenderer extends AbstractMapRenderer
{
	private $mRootElement, $mDeclaration;

	public function __construct(IRequest $Request, $Map) {
		parent::__construct($Request, $Map);
	}

	protected function renderNamedValue($name, $value) {
		echo RI::ni(), "<", $name, ">";
		$ret = parent::renderNamedValue($name, $value);
		echo RI::ni(), "</", $name, ">";

		return $ret;
	}

	protected function renderValue($value) {
		echo RI::ni(), "<", $this->mRootElement, ">";
		$ret = parent::renderValue($value);
		echo RI::ni(), "</", $this->mRootElement, ">";

		return $ret;
	}


	protected function renderStart($isArray) {
		if ($this->mDeclaration === true)
			echo "<?xml version='1.0' encoding='UTF-8'?>", RI::ni();
		elseif (is_string($this->mDeclaration))
			echo $this->mDeclaration, RI::ni();

		echo "<", $this->mRootElement, ">";
		RI::ai(1);
	}

	protected function renderEnd($isArray) {
		if ($isArray) {
			RI::ai(-1);
			echo RI::ni(), "</", $this->mRootElement, ">";

		} else {
			RI::ai(-1);
			echo RI::ni(), "</", $this->mRootElement, ">";
		}
	}

	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
		$this->mDeclaration = $declaration;
		$this->mRootElement = $rootElementName;
		parent::renderXML($Request, $rootElementName, $declaration);
		$this->mDeclaration = null;
		$this->mRootElement = null;
	}
}