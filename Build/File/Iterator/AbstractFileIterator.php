<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 2:41 PM
 */
namespace CPath\Build\File\Iterator;

use CPath\Build\File;

abstract class AbstractFileIterator implements File\Iterator\IFileIterator
{
    private $mFoundFiles = array();
    private $mFoundDirs = array();

    public function __construct($paths)
    {
        foreach((array)$paths as $path) {
            $path = rtrim(str_replace('\\', '/', $path), '/');
            $this->mFoundDirs[] = $path;
        }
    }

    /**
     * @param String $filePath
     * @param bool $isDir
     * @return bool true if the file or directory should be filtered out (removed from the results)
     */
    abstract protected function filter($filePath, $isDir);

    private function scan($dir) {
        foreach (scandir($dir) as $fileName) {
            if (in_array($fileName, array('.', '..')) || $fileName[0] === '.')
                continue;
            $filePath = $dir . '/' . $fileName;
            $isDir = is_dir($filePath);

            if ($this->filter($filePath, $isDir))
                continue;

            if ($isDir) {
                // Check for duplicates
                if(!in_array($filePath, $this->mFoundDirs))
                    $this->mFoundDirs[] = $filePath;
                continue;
            }

            $this->mFoundFiles[] = $filePath;
        }
    }

    /**
     * Return the next file in the sequence or null if no more files are available
     * @return String|null the full file path or null
     */
    function getNextFile() {
        if ($this->mFoundFiles) {
            return array_pop($this->mFoundFiles);
        }

        if ($this->mFoundDirs) {
            $dir = array_pop($this->mFoundDirs);
            $this->scan($dir);
            return $this->getNextFile();
        }

        return null;
    }
}