<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Model\DB\Interfaces;

use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;
use CPath\Model\DB\SearchResponse;

interface IPDOModelSearchRender {

    /**
     * Render a PDOModel instance on successful API_Get execution
     * @param IRequest $Request the IRequest instance for this render
     * @param SearchResponse $Response
     * @return void
     */
    function renderSearch(IRequest $Request, SearchResponse $Response);

    /**
     * Render with no PDOModel instance
     * @param \Exception $Exception the exception to render
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderException(\Exception $Exception, IRequest $Request);
}