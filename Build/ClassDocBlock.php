<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 10:07 PM
 */
namespace CPath\Build;

class ClassDocBlock extends AbstractDocBlock
{
    /** @var DocTag[] */
    private $mClass;

    public function __construct($class) {
        $this->mClass = $class;
    }

    protected function getDocComment() {
        $Class = new \ReflectionClass($this->mClass);
        return $Class->getDocComment();
    }
}