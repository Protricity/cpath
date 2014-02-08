<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\Request\Interfaces\IRequest;

interface IPDOModelRender {

    /**
     * Render a PDOModel instance on successful API_Get execution
     * @param PDOModel $Model the model instance to render
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderModel(PDOModel $Model, \CPath\Framework\Request\Interfaces\IRequest $Request);

    /**
     * Render with no PDOModel instance
     * @param \Exception $Exception the exception to render
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderException(\Exception $Exception, IRequest $Request);
}