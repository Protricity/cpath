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
use CPath\Model\DB\SearchResponse;

interface ISearchTheme {

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param SearchResponse $Response the SearchResponse instance for this query
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderSearchContent(IRequest $Request, SearchResponse $Response, IAttributes $Attr = NULL);
}