<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Routable;

use CPath\Framework\PDO\Interfaces\IPDOModelRender;
use CPath\Framework\PDO\Interfaces\IPDOModelSearchRender;
use CPath\Framework\PDO\Interfaces\ISecurityPolicyAggregate;

interface IPDORoutable extends \CPath\Route\IRouteMap, ISecurityPolicyAggregate, IPDOModelRender, IPDOModelSearchRender {

}

