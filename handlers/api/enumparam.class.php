<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;



class EnumParam extends EnumField  {
    public function __construct($description, $_enumValues, $isRequired=false, $isParam=false) {
        parent::__construct($description, $_enumValues, $isRequired, true);
    }
}
