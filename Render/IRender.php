<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/2/14
 * Time: 10:57 PM
 */
namespace CPath\Render;

use CPath\Request\IRequest;

interface IRender
{
    /**
     * Renders a response object or returns false
     * @param IRequest $Request the IRequest instance for this render
     * @return bool returns false if no rendering occurred
     */
    function render(IRequest $Request);
}