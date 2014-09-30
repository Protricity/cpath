<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:45 PM
 */
namespace CPath\Request\Executable;

class OptionalParameter implements IRequestParameter
{
    private $mName;
    private $mDescription;

    public function __construct($paramName, $description=null) {
        $this->mName = $paramName;
        $this->mDescription = $description;
    }

    /**
     * Get the request parameter name
     * @return String
     */
    function getName() {
        return $this->mName;
    }

    /**
     * Get the request parameter name
     * @return String
     */
    function getDescription() {
        return $this->mDescription;
    }
}