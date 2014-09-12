<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/9/14
 * Time: 1:51 PM
 */
namespace CPath\Build;


class MethodEditor
{
    private $mMethod;

    public function __construct(\ReflectionMethod $Method) {
        $this->mMethod = $Method;
    }

    private function readSourceBlock($start_line = 0, $end_line = null) {
        $filename = $this->mMethod->getFileName();

        $handle = @fopen($filename, "r");
        $i = -1;
        $lines = array();
        while (($line = fgets($handle, 4096)) !== false) {
            $i++;
            if ($i < $start_line)
                continue;
            if (is_int($end_line) && $i > $end_line)
                break;
            $lines[] = $line;
        }

        return $lines;
    }

    private function getMethodSourceBlock() {
        $methodBlock = $this->readSourceBlock(
            $this->mMethod->getStartLine() - 1,
            $this->mMethod->getEndLine()
        );

        return implode("\n", $methodBlock);
    }

    public function getMethodSource() {
        $methodBlock = $this->getMethodSourceBlock();

        if (!preg_match('/^([^{]+{)(.*)(}[^}]*)/', $methodBlock, $matches))
            throw new \InvalidArgumentException("Method source has invalid format");

        list(, $methodPreBody, $methodBody, $methodPostBody) = $matches;
        return $methodBody;
    }

    public function replaceMethodSource($newBody) {
        $methodBlock = $this->getMethodSourceBlock();

        if (!preg_match('/^([^{]+{)(.*)(}[^}]*)$/', $methodBlock, $matches))
            throw new \InvalidArgumentException("Method source has invalid format");

        list(, $methodPreBody, $methodBody, $methodPostBody) = $matches;

        if ($newBody == $methodBody)
            return false;

        //$newMethodBlock = $methodPreBody . $newBody . $methodPostBody;

        $newFileSource = $this->readSourceBlock(0, $this->mMethod->getStartLine() - 1);
        $newFileSource .= $methodPreBody . $newBody . $methodPostBody;
        $newFileSource .= $this->readSourceBlock($this->mMethod->getEndLine());

        file_put_contents($this->mMethod->getFileName(), $newFileSource);
        return true;
    }
}

