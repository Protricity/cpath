<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Request\IRequest;

interface IPDOModelRender {

    /**
     * Render a PDOModel instance on successful GetAPI execution
     * @param PDOModel $Model the model instance to render
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderModel(PDOModel $Model, IRequest $Request);

    /**
     * Render with no PDOModel instance
     * @param \Exception $Exception the exception to render
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderException(\Exception $Exception, IRequest $Request);
}