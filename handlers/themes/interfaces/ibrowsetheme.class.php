<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Handlers\Interfaces\IAttributes;
use CPath\Interfaces\IRequest;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;

interface IBrowseTheme {
    /**
     * Render the results of a query.
     * @param IRequest $Request the IRequest instance for this render
     * @param PDOSelect $Query query instance to render (not yet executed)
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderBrowseContent(IRequest $Request, PDOSelect $Query, IAttributes $Attr = NULL);
}