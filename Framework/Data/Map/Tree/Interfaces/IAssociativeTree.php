<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 8:44 AM
 */
namespace CPath\Framework\Data\Map\Tree\Interfaces;

use CPath\Framework\Data\Map\Associative\Interfaces\IAssociativeMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;

interface IAssociativeTree extends IAssociativeMap
{
    /**
     * Map data to subsection
     * @param $subsectionKey
     * @param IMappable $Mappable
     * @return void
     */
    function mapSubsection($subsectionKey, IMappable $Mappable);
}


