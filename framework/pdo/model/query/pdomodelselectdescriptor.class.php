<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Model\Query;

use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOSelectStats;

class PDOModelSelectDescriptor implements ISelectDescriptor {
    private $mModel, $mAPI, $mQuery, $mStatsCache;

    function __construct(PDOModel $Model, PDOSelect $Query, IAPI $API) {
        $this->mModel = $Model;
        $this->mQuery = $Query;
        $this->mAPI = $API;
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
     * @return PDOColumn
     */
    function getColumn($columnName) {
        $Model = $this->mModel;
        return $Model::loadColumn($columnName);
    }

    /**
     * Return the API used for this query
     * @return IAPI
     */
    function getAPI() {
        return $this->mAPI;
    }
}