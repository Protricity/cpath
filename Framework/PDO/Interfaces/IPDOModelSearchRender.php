<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\Response\PDOSearchResponse;
use CPath\Request\IRequest;

interface IPDOModelSearchRender {

    /**
     * Render a PDOModel instance on successful GetAPI execution
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Framework\PDO\Response\PDOSearchResponse $Response
     * @return void
     */
    function renderSearch(IRequest $Request, PDOSearchResponse $Response);

    /**
     * Render with no PDOModel instance
     * @param \Exception $Exception the exception to render
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderException(\Exception $Exception, IRequest $Request);
}