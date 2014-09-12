<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 2:41 PM
 */
namespace CPath\Build\File\Iterator;

interface IFileIterator
{
    /**
     * Return the next file in the sequence or null if no more files are available
     * @return String|null the full file path or null
     */
    function getNextFile();
}