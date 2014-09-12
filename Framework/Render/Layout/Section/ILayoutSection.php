<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/14
 * Time: 3:38 PM
 */
namespace CPath\Framework\Render\Layout\Section;

use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Render\HTML\IRenderHTML;

interface ILayoutSection extends IRenderHTML
{

    /**
     * Add section content
     * @param IRenderHTML $Content
     * @param null|String $key optional index key for this content
     * @return $this
     */
    function addContent(IRenderHTML $Content, $key = null);

    /**
     * Prepend section content
     * @param IRenderHTML $Content
     * @param null|String $key optional index key for this content
     * @return $this
     */
    function prependContent(IRenderHTML $Content, $key = null);


    /**
     * Return content by index or key
     * @param int|String $index index or string key for content
     * @return IRenderHTML
     */
    function getContent($index);

}

interface IRenderContainerHTML extends IRenderHTML {
    /**
     * Add section content
     * @param IRenderHTML $Content
     * @return $this
     */
    function addContent(IRenderHTML $Content);
}