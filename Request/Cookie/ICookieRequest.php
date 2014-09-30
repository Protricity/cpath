<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 5:28 PM
 */
namespace CPath\Request\Cookie;

use CPath\Request\IRequest;

interface ICookieRequest extends IRequest
{
    /**
     * Get a cookie
     * @param String $name retrieves &$[Cookie][$name]
     * @return String|null
     */
    function getCookie($name);

    /**
     * Set a cookie
     * @param $name
     * @param string $value
     * @param int $maxage
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $HTTPOnly
     * @return bool
     */
    function sendCookie($name, $value = '', $maxage = 0, $path = '', $domain = '', $secure = false, $HTTPOnly = false);
}