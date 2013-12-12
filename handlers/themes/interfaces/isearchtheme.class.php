<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Interfaces\IRequest;
use CPath\Model\DB\PDOSelect;

interface ISearchTheme {

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param PDOSelect $Query query instance to render (not yet executed)
     * @param String|Null $className optional class name for this section
     * @return void
     */
    function renderSearchContent(IRequest $Request, PDOSelect $Query, $className=NULL);
}