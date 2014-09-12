<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 5:14 PM
 */
namespace CPath\Build;

class MethodDocBlock extends AbstractDocBlock
{
    /** @var DocTag[] */
    private $mMethod;

    public function __construct(\ReflectionMethod $Method) {
        $this->mMethod = $Method;
    }

    protected function getDocComment() {
        return $this->mMethod->getDocComment();
    }
}

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