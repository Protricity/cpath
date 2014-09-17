<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/24/14
 * Time: 11:47 AM
 */
namespace CPath\Request;

interface IStaticRequestHandler {
    /**
     * Handle this request and render any content
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    static function handleStaticRequest(IRequest $Request);
}
