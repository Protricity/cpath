<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/8/2015
 * Time: 2:10 PM
 */
namespace CPath\Build;

class PropertyDocBlock extends AbstractDocBlock
{
	/** @var DocTag[] */
	private $mProperty;

	public function __construct(\ReflectionProperty $Property) {
		$this->mProperty = $Property;
	}

	protected function getDocComment() {
		return $this->mProperty->getDocComment();
	}
}