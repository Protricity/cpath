<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Query;
use CPath\Config;
use CPath\Data\Map\IKeyMapper;

class PDOSelectStats extends PDOSelectLimitedStats {

    const PAGE_COUNT_DEFAULT = 25;

    private $total, $previousPage, $nextPage, $totalPages, $hasMore;
//, $mPageIDs = null;

    function __construct($totalCount, $limit, $offset)
    {
        parent::__construct($limit, $offset);

        $totalPages = (int)floor($totalCount / $limit) + 1;

        $this->previousPage = $this->getCurPage() - 1;
        if($this->previousPage <= 0)
            $this->previousPage = null;

        $this->nextPage = $this->getCurPage() + 1;
        if($this->nextPage > $totalPages)
            $this->nextPage = null;

        $this->hasMore = $totalPages != $this->getCurPage();
        $this->total = $totalCount;
        $this->totalPages = $totalPages;


    }

    public function getHasMore()
    {
        return $this->hasMore;
    }

    public function getPreviousPage()
    {
        return $this->previousPage;
    }

    public function getNextPage()
    {
        return $this->nextPage;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function getPageIDs($count=NULL) {
        $tp = $this->totalPages;
        $cur = $this->getCurPage();

        if($count) {
            //$this->mPageIDs = null;
        } else {
            $count = self::PAGE_COUNT_DEFAULT;
        }
        $ids = array($cur => $cur);
        if(!isset($ids[1]))
            $ids[1] = 1;
        if(!isset($ids[$tp]))
            $ids[$tp] = $tp;

        if($tp < $count) {
            for($i=1; $i<=$tp; $i++)
                if(!isset($ids[$i]))
                    $ids[$i] = $i;
        } else {
            $r = 1;
            $i = 2;
            $s = sizeof($ids);
            while($s < $count) {


                $done = true;
                if(($p = $cur - $i) > 0 && !isset($ids[$p])) {
                    $ids[$p] = $p;
                    $s++;
                    $done = false;
                }
                if($s < $count && ($p = $cur + $i) < $tp && !isset($ids[$p])) {
                    $ids[$p] = $p;
                    $s++;
                    $done = false;
                }
                if($done)
                    break;
                $i+=$r;
                $r++;
            }
        }

        sort($ids);
        //if($this->nextPage && !isset($ids[$this->nextPage]))
        //    $ids = $ids + array($this->nextPage => 'Next');
        //if($this->previousPage && !isset($ids[$this->previousPage]))
        //    $ids = array($this->previousPage => 'Previous') + $ids;
        //return $this->mPageIDs = $ids;
        return $ids;
    }

	/**
	 * Map data to a data map
	 * @param IRequest $Request
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Framework\PDO\Query\IRequest $Request
	 * @return void
	 */
    function mapKeys(IRequest $Request, IKeyMapper $Map) {
        parent::mapKeys($Request, $Map);

        foreach($this as $k=>$v)
            $Map->map($k, $v);

        $pages = array();
        foreach($this->getPageIDs() as $k=>$v) {
            $pages[] = array('page' => $v, 'id' => $k);
//            $Map->mapDataToKey($k, $v);
//            $Map->mapDataToKey($k, $v);
//            $child = $xml->addChild('page', $v);
//            $child->addAttribute('id', $k);
        }
        $Map->map('pages', $pages);
    }
}