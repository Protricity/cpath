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
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;

interface IPDOModelSearchRender {

    /**
     * Render a PDOModel instance on successful API_Get execution
     * @param PDOSelect $Query the query instance to render
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderSearch(PDOSelect $Query, IRequest $Request);

    /**
     * Render with no PDOModel instance
     * @param \Exception $Exception the exception to render
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderException(\Exception $Exception, IRequest $Request);
}