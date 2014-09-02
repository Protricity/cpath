<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/31/14
 * Time: 2:25 PM
 */
namespace CPath\Framework\Render\Header\Interfaces;

use CPath\Framework\Render\Header\Interfaces\IHeaderWriter;

interface ISupportHeaders
{
    /**
     * Write all support headers used by this IView instance
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head);
}