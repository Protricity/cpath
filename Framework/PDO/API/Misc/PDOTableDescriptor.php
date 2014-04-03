<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 4/3/14
 * Time: 9:16 AM
 */

namespace CPath\Framework\PDO\API\Misc;

use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
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
        $Map->mapKeyValue('page', $Stats->getCurPage());
        $Map->mapKeyValue('previous', $Stats->getPreviousPage());
        $Map->mapKeyValue('next', $Stats->getNextPage());
        $Map->mapKeyValue('offset', $Stats->getOffset());
        $Map->mapKeyValue('limit', $Stats->getLimit());
        $Map->mapKeyValue('total', $Stats->getTotal());
        $Map->mapKeyValue('total_pages', $Stats->getTotalPages());
        $Map->mapKeyValue('has_more', $Stats->getHasMore());
        $Map->mapKeyValue('page_ids', $Stats->getPageIDs());
        $Map->mapKeyValue('url', $Stats->getURL());
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