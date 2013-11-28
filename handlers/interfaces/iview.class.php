<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Interfaces;


use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;

interface IView extends IHandler {

    /**
     * Render the view body
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderBody(IRequest $Request);

    function renderHead(IRequest $Request);

    function getTarget();

    function getTheme();

    function getBasePath($appendPath=NULL);

    function setTitle($title);

    function addHeadHTML($html, $key=null, $replace=false);

    function addHeadScript($src, $key=null, $replace=false);

    function addHeadStyleSheet($href, $key=null, $replace=false);
}