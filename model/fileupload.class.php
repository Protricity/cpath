<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Model;

use CPath\Interfaces\FileTransferFailedException;
use CPath\Interfaces\IFileTransfer;

class FileUploadException extends \Exception {}
class NoUploadFoundException extends FileUploadException {}
class InvalidUploadException extends FileUploadException {}

class FileUpload implements IFileTransfer{
    private $mLocalPath = null;
    private $mIsTransferred = false;
    private $mName, $mMimeType, $mTmpName, $mError, $mSize;
    protected function __construct($name, $type, $tmp_name, $error, $size) {
        if(!$tmp_name || !$name)
            throw new InvalidUploadException("File upload info is empty");
        $this->mName = $name;
        $this->mMimeType = $type;
        $this->mTmpName = $tmp_name;
        $this->mError = $error;
        $this->mSize = $size;
    }


    /**
     * Process the file transfer
     * If NULL, the path provided in ->setLocalPath() will be used.
     * @return void
     * @throws FileTransferFailedException if the transfer failed or was not configured correctly
     */
    function processTransfer() {
        if($this->mError)
            throw new FileTransferFailedException("An Error occured: " . $this->mError);
        if(!$this->mLocalPath)
            throw new FileTransferFailedException("Local Path was not set for this transfer. Use setLocalPath()");
        $result = move_uploaded_file($this->mTmpName, $this->mLocalPath);
        if(!$result)
            throw new FileTransferFailedException("File transfer failed from ({$this->mTmpName}) to ({$this->mLocalPath}).");
        $this->mIsTransferred = true;
    }

    /**
     * Returns true if the transfer has been complete
     */
    function isTransferred() {
        return $this->mIsTransferred;
    }

    /**
     * Get the file name
     * @return String
     */
    function getName() {
        return $this->mName;
    }

    /**
     * Get the file transfer source uri
     * @return String the file transfer source
     */
    function getSource() {
        return 'local://' . $this->mTmpName;
    }

    /**
     * Get the mime type of the file
     * @return String the mime-type
     */
    function getMimeType() {
        return $this->mMimeType;
    }

    /**
     * Get the file size in bytes
     * @return int file size in bytes.
     */
    function getSize() {
        return $this->mSize;
    }

    /**
     * Get file creation date in unix time
     * @return int|NULL creation date in unix time or null
     */
    function getCreateDate() {
        return null;
    }

    /**
     * Get the local path of the transfer.
     * @return mixed
     * @throws FileTransferFailedException if the path is not available yet or the transfer has not occurred
     */
    function getLocalPath() {
        if(!$this->mLocalPath)
            throw new FileTransferFailedException("Local path has not been set.");
        return $this->mLocalPath;
    }

    /**
     * Set the local path of the transfer.
     * @param String $localPath the new local path for this transfer.
     * @return void
     * @throws FileTransferFailedException if the path is not available yet or the transfer has not occurred
     */
    function setLocalPath($localPath)
    {
        if($this->mLocalPath)
            throw new FileTransferFailedException("Local path has already been set.");
        $this->mLocalPath = $localPath;
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
            try {
                $Files = new FileUpload($a, $b, $c, $d, $e);
            } catch (InvalidUploadException $ex) {
                $Files = NULL;
            }
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