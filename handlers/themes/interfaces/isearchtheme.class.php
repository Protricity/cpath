<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelect;
use CPath\Model\DB\SearchResponse;

interface ISearchTheme {

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param SearchResponse $Response the SearchResponse instance for this query
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderSearchContent(IRequest $Request, SearchResponse $Response, $class = NULL, $attr = NULL);
}