<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 5:50 PM
 */
namespace CPath\Request\Parameter;

use CPath\Request\Parameter\IMappableParameters;

interface IParameterMap
{
    /**
     * Map request parameters for this object
     * @param IMappableParameters $Map
     * @return void
     */
    function mapParameters(IMappableParameters $Map);
}