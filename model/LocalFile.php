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
use CPath\Model\Mime\MimeTypes;

class LocalFileNotFoundException extends \Exception {}

class LocalFile implements IFileTransfer{
    private $mSourceFile = null;
    private $mDestFile = null;
    private $mIsTransferred = false;
    public function __construct($sourceFile) {
        if(!file_exists($sourceFile))
            throw new LocalFileNotFoundException("File '" . $sourceFile . "' was not found");
        $this->mSourceFile = $sourceFile;
    }


    /**
     * Process the file transfer
     * If NULL, the path provided in ->setLocalPath() will be used.
     * @return void
     * @throws FileTransferFailedException if the transfer failed or was not configured correctly
     */
    function processTransfer() {
        if(!$this->mDestFile)
            throw new FileTransferFailedException("Local path has not been set for local files.");
        $result = copy($this->mSourceFile, $this->mDestFile);
        if(!$result)
            throw new FileTransferFailedException("File transfer failed from ({$this->mSourceFile}) to ({$this->mDestFile}).");
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
        return basename($this->mSourceFile);
    }

    /**
     * Get the file transfer source uri
     * @return String the file transfer source
     */
    function getSource() {
        return 'local://' . $this->mSourceFile;
    }

    /**
     * Get the mime type of the file
     * @return String the mime-type
     */
    function getMimeType() {
        return MimeTypes::get($this->mSourceFile);
    }

    /**
     * Get the file size in bytes
     * @return int file size in bytes.
     */
    function getSize() {
        return filesize($this->mSourceFile);
    }

    /**
     * Get file creation date in unix time
     * @return int|NULL creation date in unix time or null
     */
    function getCreateDate() {
        return filectime($this->mSourceFile);
    }

    /**
     * Get the local path of the transfer.
     * @return mixed
     * @throws FileTransferFailedException if the path is not available yet or the transfer has not occurred
     */
    function getLocalPath() {
        return $this->mIsTransferred ? $this->mDestFile : $this->mSourceFile;
    }

    /**
     * Set the local path of the transfer.
     * @param String $destFile the new local path for this transfer.
     * @return void
     * @throws FileTransferFailedException if the path is not available yet or the transfer has not occurred
     */
    function setLocalPath($destFile) {
        $this->mDestFile = $destFile;
    }

}