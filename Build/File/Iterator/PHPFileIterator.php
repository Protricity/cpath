<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 2:41 PM
 */
namespace CPath\Build\File\Iterator;

class PHPFileIterator extends AbstractFileIterator
{
    private $mNSFilter;

    public function __construct($namespaceFilter, $paths) {
        $this->mNSFilter = $namespaceFilter;
        parent::__construct($paths);
    }

    /**
     * @param $filePath
     * @param $isDir
     * @return bool true if the file or directory should be filtered out (removed from the results)
     */
    protected function filter($filePath, $isDir) {
        if ($isDir)
            return false;

        if (substr($filePath, -4) !== '.php') {
            return true;
        }

        return false;
    }

}