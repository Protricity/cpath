<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/14
 * Time: 11:39 AM
 */
namespace CPath\Build;

use CPath\Request\CLI\CommandString;

class DocTag
{
    private $mName, $mArgString;
    private $mOffset = 0;

    public function __construct($name, $content) {
        $this->mName = $name;
        $this->mArgString = $content;
    }

    public function getName() {
        return $this->mName;
    }

    public function getArgString() {
        return $this->mArgString;
    }

    public function getNextArg() {
        $pos = strpos($this->mArgString, ' ', $this->mOffset);
        if($pos === false)
            return null;
        $arg = substr($this->mArgString, $this->mOffset ?: 0, $pos);
        $this->mOffset = $pos + 1;
        return $arg;
    }

    public function getFullTag() {
        return '@' . $this->getName() . ' ' . $this->getArgString();
    }

    public function __toString() {
        return $this->getFullTag();
    }
}