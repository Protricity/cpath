<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Request\Interfaces\IRequest;

interface IBrowseTheme {
    /**
     * Render the results of a query.
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Framework\PDO\Query\PDOSelect $Query query instance to render (not yet executed)
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderBrowseContent(IRequest $Request, PDOSelect $Query, \CPath\Framework\Render\Attribute\IAttributes $Attr = NULL);
}