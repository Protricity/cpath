<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/24/14
 * Time: 11:47 AM
 */
namespace CPath\Framework\Render;

use CPath\Framework\Request\Interfaces\IRequest;

interface IRender
{
    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request);
}