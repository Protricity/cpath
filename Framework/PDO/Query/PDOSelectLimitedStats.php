<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Query;
use CPath\Config;
use CPath\Framework\Data\Map\Associative\Interfaces\IAssociativeMap;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;

class PDOSelectLimitedStats implements IMappable {

    private $limit, $offset, $curPage;

    function __construct($limit, $offset)
    {
        $this->curPage = (int)floor($offset / $limit) + 1;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getCurPage()
    {
        return $this->curPage;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getURL($page=NULL, $limit=NULL) {
        $params = is_array($limit) ? $limit : get_defined_vars();
        if(!$params['limit'])   $params['limit'] = $this->limit;
        if(!$params['page']) $params['page'] = $this->curPage;
        //if($params['page'] == $this->curPage)
        //    unset($params['page']);
        return '?' . http_build_query($params);
    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map) {
        foreach($this as $k=>$v)
            $Map->mapKeyValue($k, $v);
    }
}