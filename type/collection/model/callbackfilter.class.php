<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Type\Collection\Model;

use CPath\Type\Collection\ICollectionFilter;

class CallbackFilter implements ICollectionFilter {

    public function __construct($callable);
}