<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 3:29 PM
 */
namespace CPath\Request\Parameter;

interface IMappableParameters
{
    //const REQUIRED = 0x01;

    /**
     * @param Parameter $Parameter
     * @return mixed
     */
    function map(Parameter $Parameter);
}