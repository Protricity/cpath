<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Interfaces;


use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;

interface IView extends \CPath\Framework\Render\IRender {

    /**
     * Render the html body
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderBody(IRequest $Request);

    /**
     * Render the html head
     * @param IRequest $Request
     * @return mixed
     */
    function renderHead(IRequest $Request);

    /**
     * Return the view theme or null if none exists
     * @return mixed
     */
    function getTheme();

    /**
     * Get the base path of the target class
     * @param null $appendPath
     * @return mixed
     */
    function getBasePath($appendPath=NULL);

    /**
     * Set the view title
     * @param $title
     * @return mixed
     */
    function setTitle($title);

    /**
     * Add html to the head
     * @param String $html the string to add
     * @param bool $replace set to true if replacing an existing item
     * @return mixed
     */
    function addHeadHTML($html, $replace=false);

    /**
     * Add a javascript entry to the head
     * @param $src
     * @param bool $replace
     * @return mixed
     */
    function addHeadScript($src, $replace=false);

    /**
     * Add a css stylesheet to the head
     * @param $href
     * @param bool $replace
     * @return mixed
     */
    function addHeadStyleSheet($href, $replace=false);
}