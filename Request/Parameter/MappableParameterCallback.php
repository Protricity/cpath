<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 4:16 PM
 */
namespace CPath\Request\Parameter;


use CPath\Request\Parameter\IMappableParameters;
use CPath\Request\Parameter\Parameter;

class MappableParameterCallback implements IMappableParameters
{
    private $mCallback;

    public function __construct(\Closure $callback) {
        $this->mCallback = $callback;
    }

    /**
     * @param Parameter $Parameter
     * @return mixed
     */
    function map(Parameter $Parameter) {
        $call = $this->mCallback;
        $call($Parameter);
    }
}