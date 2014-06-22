<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Request\Interfaces;
use CPath\Interfaces\IArrayObject;
use CPath\Model\FileUpload;

interface IRequest extends IArrayObject {

    /**
     * Get the URL Path starting at the root path of the framework
     * @return String the url path starting with '/'
     */
    function getPath();

    /**
     * @return Array
     */
    function getArgs();

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String the method
     */
    function getMethod();

    /**
     * Returns Request headers
     * @param String|Null $key the header key to return or all headers if null
     * @return mixed
     */
    function getHeaders($key=NULL);

    /**
     * Returns a list of mimetypes accepted by this request
     * @return Array
     */
    function getMimeTypes();

    /**
     * Merges an associative array into the current request
     * @param array $request the array to merge
     * @param boolean $replace if true, the array is replaced instead of merged
     * @return void
     */
    function merge(Array $request, $replace=false);

    /**
     * Remove an element from the request array and return its value
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->pluck(0, 'key') removes $data[0]['key'] and returns it's value;
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException if the data path doesn't exist
     */
    function pluck($_path);

    /**
     * Returns a file upload by name, or throw an exception
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getFileUpload(0, 'key') gets $_FILES[0]['key'] formatted as a FileUpload instance;
     * @return FileUpload
     * @throws \InvalidArgumentException if the file was not found
     */
    function getFileUpload($_path=NULL);

    // Statics

    //static function fromRequest(); // TODO: ugly
}