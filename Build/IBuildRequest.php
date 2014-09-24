<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/8/14
 * Time: 5:25 PM
 */
namespace CPath\Build;

use CPath\Request\IFlaggedRequest;
use CPath\Request\Executable\IPrompt;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;

interface IBuildRequest extends IRequest, IFlaggedRequest
{
    const IS_SESSION_BUILD = 0x1;
    //const USE_DEFAULTS = 0x2;
    const TEST_MODE = 0x4;

    /**
     * Get the build ID
     * @return int
     */
    function getBuildID();
}
