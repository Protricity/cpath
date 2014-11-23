<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 3:39 PM
 */
namespace CPath\Render\HTML\Attribute;

class ClassAttributes extends Attributes
{
    public function __construct($classList, $_classList = null) {
        foreach (func_get_args() as $arg)
	        if($arg)
                $this->addClass($arg);
    }
}

