<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;

use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;

class File implements IRender{
    const BUILD_IGNORE = true;

    private $mFilePath;

    public function __construct($filePath) {
        $this->mFilePath = $filePath;
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function render(IRequest $Request)
    {
        include($this->mFilePath);
    }
}