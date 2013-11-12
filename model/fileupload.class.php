<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Model;

class FileUploadException extends \Exception {}
class NoUploadFoundException extends FileUploadException {}
class InvalidUploadException extends FileUploadException {}

class FileUpload {
    private $mName, $mType, $mTmpName, $mError, $mSize;
    protected function __construct($name, $type, $tmp_name, $error, $size) {
        $this->mName = $name;
        $this->mType = $type;
        $this->mTmpName = $tmp_name;
        $this->mError = $error;
        $this->mSize = $size;
    }

    // Statics

    public static function fromGlobal($name) {
        if(!isset($_FILES[$name]))
            throw new NoUploadFoundException("Upload was not found: " . $name);

        $data = $_FILES[$name];
        if(!isset($data['name'], $data['type']))
            throw new InvalidUploadException("Upload failed. No 'name' or 'type' found");

        if(!is_scalar($data['name']))
            throw new InvalidUploadException("Upload failed. 'name' is not a scalar");

        return new FileUpload(
            $data['name'],
            $data['type'],
            $data['tmp_name'],
            $data['error'],
            $data['size']
        );
    }

    public static function getAll() {
        static $Files = null;
        if($Files !== null)
            return $Files;
        $Files = array();
        foreach($_FILES as $name => $data1) {
            $Files[$name] = array();
            self::_recurse($data1['name'], $data1['type'], $data1['tmp_name'], $data1['error'], $data1['size'], $Files[$name]);
        }
        return $Files;
    }

    private static function _recurse($a, $b, $c, $d, $e, &$Files) {
        if(is_scalar($a)) {
            $Files = new FileUpload($a, $b, $c, $d, $e);
            return;
        }
        foreach($a as $key=>$value) {
            if(is_array($Files))
                $Files = array();
            if(!isset($Files[$key]))
                $Files[$key] = array();
            $FilesRef = &$Files[$key];
            self::_recurse($a[$key], $b[$key], $c[$key], $d[$key], $e[$key], $FilesRef);
        }
    }
}