<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;
use CPath\Model\FileUpload;

/**
 * Class FileUploadField
 * @package CPath
 */
class FileUploadField extends Field {

    private $mFileWildCard, $mMimeWildCard;

    /**
     * Create a new FileUploadField
     * @param String|IDescribable $Description
     * @param string $fileWildCard filter files by a file wild card
     * @param string $mimeWildCard filter files by mime type
     */
    public function __construct($Description=NULL, $fileWildCard='*.*', $mimeWildCard='*/*') {
        parent::__construct($Description);
        $this->mFileWildCard = $fileWildCard;
        $this->mMimeWildCard = $mimeWildCard;
    }

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return FileUpload|Array an instance of the file upload data or an array of instances
     * @throws ValidationException if validation fails
     */
    function validate(IRequest $Request, $fieldName) {
        $File = $Request->getFileUpload($fieldName);
        if($File && $Request[$fieldName])
            throw new ValidationException("File upload was replaced by " . Describable::get($Request[$fieldName]));
        if(!$File && $Request[$fieldName])
            $File = $Request[$fieldName];

        $this->validateRequired($File);
        if($this->mFileWildCard && !fnmatch($this->mFileWildCard, $File->getName()))
            throw new ValidationException("File name '{$File->getName()}' does not match Wildcard: " . $this->mFileWildCard);
        if($this->mMimeWildCard && !fnmatch($this->mMimeWildCard, $File->getMimeType()))
            throw new ValidationException("Mime type '{$File->getMimeType()}' does not match Wildcard: " . $this->mMimeWildCard);
        return $File;
    }

    /**
     * Render this input field as html
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request) {
        echo RI::ni(), "<input type='file' name='{$this->getName()}' accept='{$this->mMimeWildCard}' placeholder='Enter value for {$this->getName()}' />";
    }
}