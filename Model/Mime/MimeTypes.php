<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Model\Mime;

class MimeTypeNotFoundException extends \Exception {}

abstract class MimeTypes {
    /**
     * Get the mime type of a file by extension
     * @param $filePath
     * @return String
     * @throws MimeTypeNotFoundException if no mime type was found
     */
    abstract function getByFilePath($filePath);

    /**
     * Get the mime type of a file by extension
     * @return bool true if this method is available
     */
    abstract function available();

    // Statics

    /**
     * (Shorthand) Get a mime type from a file name or return the MimeTypes inst
     * @param String|null $path if passed, the mime type string is returned, otherwise the MimeTypes inst is returned
     * @return MimeTypes|String
     * @throws MimeTypeNotFoundException if no mime type was found
     */
    static function get($path=NULL) {
        static $inst = NULL;
        if(!$inst) {
            $i = new MimeTypesFInfo();
            if(!$i->available())
                $i = new MimeTypesOld();
            if(!$i->available())
                $i = new MimeDB();
            if(!$i->available())
                throw new MimeTypeNotFoundException("No MimeType Methods Available");
            $inst = $i;
        }
        return $path ? $inst->getByFilePath($path) : $inst;
    }
}

final class MimeTypesFInfo extends MimeTypes{

    /**
     * Get the mime type of a file by extension
     * @param $filePath
     * @return String
     * @throws MimeTypeNotFoundException if no mime type was found
     */
    function getByFilePath($filePath) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mime = finfo_file($finfo, $filePath);
        if($mime)
            throw new MimeTypeNotFoundException("Mime type was not found for: " . $filePath);
        return $mime;
    }

    /**
     * Get the mime type of a file by extension
     * @return bool true if this method is available
     */
    function available() {
        return function_exists('finfo_open');
    }
}

final class MimeTypesOld extends MimeTypes{

    /**
     * Get the mime type of a file by extension
     * @param $filePath
     * @return String
     * @throws MimeTypeNotFoundException if no mime type was found
     */
    function getByFilePath($filePath) {
        $mime = mime_content_type($filePath);
        if($mime)
            throw new MimeTypeNotFoundException("Mime type was not found for: " . $filePath);
        return $mime;
    }

    /**
     * Get the mime type of a file by extension
     * @return bool true if this method is available
     */
    function available() {
        return function_exists('mime_content_type');
    }
}