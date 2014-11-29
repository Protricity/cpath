<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/27/14
 * Time: 12:35 AM
 */
namespace CPath\Render\Helpers;

class RenderIndentsEnd
{
	private $mTab, $mCount;

	public function __construct($tabCount, $newTab) {
		$this->mTab   = $newTab;
		$this->mCount = $tabCount;
	}

	public function reset() {
		$I = \CPath\Render\Helpers\RenderIndents::get();
		$I->setIndent($this->mCount, $this->mTab);
	}
}