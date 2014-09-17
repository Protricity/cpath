<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:45 PM
 */
namespace CPath\Request\CLI;

use CPath\Request\IRequest;
use CPath\Render\Text\TextMimeType;
use CPath\Request\IRequestMethod;

final class CLIRequest implements IRequest
{
    private $mPath;
    private $mMethod;

    public function __construct(IRequestMethod $Method=null) {
        $this->mMethod = $Method ?: new CLIMethod();
        $this->mPath = $Method->prompt("Enter Request Path");
    }

    /**
     * Get the Request Method Instance (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return \CPath\Request\IRequestMethod
     */
    function getMethod() {
        return $this->mMethod;
    }

    /**
     * Get the route path
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mPath;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes() {
        return array(
            new TextMimeType(),
        );
    }
}