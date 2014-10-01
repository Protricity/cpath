<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 4/3/14
 * Time: 9:16 AM
 */

namespace CPath\Framework\PDO\API\Misc;

use CPath\Framework\API\Interfaces\IAPI;
use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IKeyMap;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOSelectStats;
use CPath\Framework\PDO\Table\Types\PDOTable;

class PDOTableDescriptor implements ISelectDescriptor, IKeyMap {
    private $mTable, $mAPI, $mQuery, $mStatsCache;

    function __construct(PDOTable $Table, PDOSelect $Query, IAPI $API) {
        $this->mTable = $Table;
        $this->mQuery = $Query;
        $this->mAPI = $API;
    }

    /**
     * Map data to a data map
     * @param IMappableKeys $Map the map instance to add data to
     * @internal param \CPath\Framework\PDO\API\Misc\IRequest $Request
     * @return void
     */
    function mapKeys(IMappableKeys $Map)
    {
        $Stats = $this->execFullStats();
        $Map->map('page', $Stats->getCurPage());
        $Map->map('previous', $Stats->getPreviousPage());
        $Map->map('next', $Stats->getNextPage());
        $Map->map('offset', $Stats->getOffset());
        $Map->map('limit', $Stats->getLimit());
        $Map->map('total', $Stats->getTotal());
        $Map->map('total_pages', $Stats->getTotalPages());
        $Map->map('has_more', $Stats->getHasMore());
        $Map->map('page_ids', $Stats->getPageIDs());
        $Map->map('url', $Stats->getURL());
    }

    public function getLimitedStats() {
        return $this->mQuery->getLimitedStats();
    }

    public function execFullStats($allowCache=true) {
        $Stats = $this->getLimitedStats();
        if(!$allowCache)
            $this->mStatsCache = NULL;
        return $this->mStatsCache ?: $this->mStatsCache = new PDOSelectStats(
            (int)$this->mQuery->execStats('count(*)')->fetchColumn(0),
            $Stats->getLimit(),
            $Stats->getOffset()
        );
    }

    /**
     * Return the column for a query row value
     * @param String $columnName the name of the column to be translated
     * @return \CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn
     */
    function getColumn($columnName) {
        return $this->mTable->getColumn($columnName);
    }

    /**
     * Return the API used for this query
     * @return IAPI
     */
    function getAPI() {
        return $this->mAPI;
    }

}