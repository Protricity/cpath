<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/14
 * Time: 12:18 PM
 */
namespace CPath\Build;

abstract class AbstractDocBlock
{
    /** @var DocTag[] */
    private $mTags = array();

    abstract protected function getDocComment();

    public function hasTag($tagName) {
        foreach ($this->mTags as $Tag)
            if ($Tag->getName() === $tagName)
                return true;
        return false;
    }

    /**
     * Returns the next available doctag from a method
     * @param String|null $tagName Optional name of the tag to parse arguments from
     * @return DocTag the next tag instance or null if no tag was found
     */
    public function getNextTag($tagName = null) {
        /** @var DocTag $Tag */
        if ($tagName === null)
            return next($this->mTags);

        if (!$tagName[0] === '@')
            $tagName = '@' . $tagName;

        while ($Tag = next($this->mTags)) {
            if ($Tag->getName() === $tagName)
                return $Tag;
        }
        return null;
    }

    /**
     * Returns all tags
     * @return array|DocTag[]
     */
    public function getAllTags() {
        if ($this->mTags !== null)
            return $this->mTags;

        $this->mTags = array();
        $doc = $this->getDocComment();
        if (preg_match_all('/@([a-zA-Z0-9_]+)\s+([^@$]+)/i', $doc, $matches)) {
            foreach ($matches[1] as $i => $tagName) {
                $this->mTags[] = new DocTag($tagName, $matches[2][$i]);
            }
        }

        return $this->mTags;
    }

    public function getComment($withoutTags = true) {
        $doc = $this->getDocComment();

        if($withoutTags)
            $doc = preg_replace('/@\w+\s+.*$/', '', $doc);

        $doc = preg_replace('/^\s+[*/]+\s+', '', $doc);

        return trim($doc);
    }
}