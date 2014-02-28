<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

class FileTransferFailedException extends \Exception {}

interface IFileTransfer {

    /**
     * Process the file transfer
     * @return void
     * @throws FileTransferFailedException if the transfer failed or was not configured correctly
     */
    function processTransfer();

    /**
     * Get the file name
     * @return String
     */
    function getName();

    /**
     * Get the file transfer source uri
     * @return String the file transfer source
     */
    function getSource();

    /**
     * Get the mime type of the file
     * @return String the mime-type
     */
    function getMimeType();

    /**
     * Get the file size in bytes
     * @return int file size in bytes.
     */
    function getSize();

    /**
     * Get file creation date in unix time
     * @return int|NULL creation date in unix time or null
     */
    function getCreateDate();

    /**
     * Get the local path of the transfer.
     * @return mixed
     * @throws FileTransferFailedException if the path is not available yet or the transfer has not occurred
     */
    function getLocalPath();

    /**
     * Set the local path of the transfer.
     * @param String $localPath the new local path for this transfer.
     * @return void
     * @throws FileTransferFailedException if the path is not available yet or the transfer has not occurred
     */
    function setLocalPath($localPath);

    /**
     * Returns true if the transfer has been complete
     */
    function isTransferred();
}
