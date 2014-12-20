<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/25/14
 * Time: 12:31 PM
 */
namespace CPath\Render\Helpers;

class CookieUtil
{

    /**
     * A better alternative (RFC 2109 compatible) to the php setcookie() function
     *
     * @param string $name Name of the cookie
     * @param string $value Value of the cookie
     * @param int $maxage Lifetime of the cookie
     * @param string $path Path where the cookie can be used
     * @param string $domain Domain which can read the cookie
     * @param bool $secure Secure mode?
     * @param bool $HTTPOnly Only allow HTTP usage?
     * @return bool True or false whether the method has successfully run
     */
    function sendCookie($name, $value = '', $maxage = 0, $path = '', $domain = '', $secure = false, $HTTPOnly = false)
    {
        $ob = ini_get('output_buffering');

        // Abort the method if headers have already been sent, except when output buffering has been enabled 
        if (headers_sent() && (bool)$ob === false || strtolower($ob) == 'off')
            return false;

        if (!empty($domain)) {
            // Fix the domain to accept domains with and without 'www.'. 
            if (strtolower(substr($domain, 0, 4)) == 'www.') $domain = substr($domain, 4);
            // Add the dot prefix to ensure compatibility with subdomains 
            if (substr($domain, 0, 1) != '.') $domain = '.' . $domain;

            // Remove port information. 
            $port = strpos($domain, ':');

            if ($port !== false) $domain = substr($domain, 0, $port);
        }

        // Prevent "headers already sent" error with utf8 support (BOM) 
        //if ( utf8_support ) header('MainContent-Type: text/html; charset=utf-8');

        header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
            . (empty($domain) ? '' : '; Domain=' . $domain)
            . (empty($maxage) ? '' : '; Max-Age=' . $maxage)
            . (empty($path) ? '' : '; Path=' . $path)
            . (!$secure ? '' : '; Secure')
            . (!$HTTPOnly ? '' : '; HttpOnly'), false);
        return true;
    }

}