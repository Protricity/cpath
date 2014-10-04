<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Field;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\API\Exceptions\ValidationException;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Model\FileUpload;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

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
     * @param \CPath\Request\IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return FileUpload|Array an instance of the file upload data or an array of instances
     * @throws \CPath\Framework\API\Exceptions\ValidationException if validation fails
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
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr=null) {
        echo RI::ni(), "<input type='file' name='{$this->getName()}' accept='{$this->mMimeWildCard}' placeholder='Enter value for {$this->getName()}' />";
    }
}