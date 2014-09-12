<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:47 PM
 */
namespace CPath\Request\Web;

final class GETRequest extends AbstractWebRequest
{

    /**
     * Get the Request Method (GET)
     * @return String
     */
    function getMethodName() {
        return 'GET';
    }
}