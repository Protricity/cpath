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

