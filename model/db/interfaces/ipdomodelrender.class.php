<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Model\DB\Interfaces;

use CPath\Handlers\Api\Interfaces\IField;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\PDOModel;

interface IPDOModelRender {

    /**
     * Render a PDOModel instance on successful API_Get execution
     * @param PDOModel $Model the model instance to render
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderModel(PDOModel $Model, IRequest $Request);

    /**
     * Render with no PDOModel instance
     * @param \Exception $Exception the exception to render
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderException(\Exception $Exception, IRequest $Request);
}