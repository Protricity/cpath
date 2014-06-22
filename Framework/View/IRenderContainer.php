<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 6/14/14
 * Time: 11:02 AM
 */
namespace CPath\Framework\View;

use CPath\Framework\Render\HTML\IRenderHTML;

interface IRenderContainer extends IRenderHTML
{
    /**
     * @param IRenderHTML $Renderer
     * @return mixed
     */
    function addRenderItem(IRenderHTML $Renderer);
}