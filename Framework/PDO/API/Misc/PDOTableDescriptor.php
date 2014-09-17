<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 4/3/14
 * Time: 9:16 AM
 */

namespace CPath\Framework\PDO\API\Misc;

use CPath\Framework\API\Interfaces\IAPI;
use CPath\Data\Map\IDataMap;
use CPath\Data\Map\IMappable;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOSelectStats;
use CPath\Framework\PDO\Table\Types\PDOTable;

class PDOTableDescriptor implements ISelectDescriptor, IMappable {
    private $mTable, $mAPI, $mQuery, $mStatsCache;

    function __construct(PDOTable $Table, PDOSelect $Query, IAPI $API) {
        $this->mTable = $Table;
        $this->mQuery = $Query;
        $this->mAPI = $API;
    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map)
    {
        $Stats = $this->execFullStats();
        $Map->mapNamedValue('page', $Stats->getCurPage());
        $Map->mapNamedValue('previous', $Stats->getPreviousPage());
        $Map->mapNamedValue('next', $Stats->getNextPage());
        $Map->mapNamedValue('offset', $Stats->getOffset());
        $Map->mapNamedValue('limit', $Stats->getLimit());
        $Map->mapNamedValue('total', $Stats->getTotal());
        $Map->mapNamedValue('total_pages', $Stats->getTotalPages());
        $Map->mapNamedValue('has_more', $Stats->getHasMore());
        $Map->mapNamedValue('page_ids', $Stats->getPageIDs());
        $Map->mapNamedValue('url', $Stats->getURL());
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