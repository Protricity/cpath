<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Query;
use CPath\Config;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IXML;

class PDOSelectLimitedStats implements IJSON, IXML {

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
     * EXPORT Object to an associative array to be formatted into JSON
     * @param Array $JSON the JSON array to modify
     * @return void
     */
    function toJSON(Array &$JSON)
    {
        foreach($this as $k=>$v)
            $JSON[$k] = $v;
    }

    /**
     * EXPORT Object to XML
     * @param \SimpleXMLElement $xml the XML instance to modify
     * @return void
     */
    function toXML(\SimpleXMLElement $xml)
    {
        foreach($this as $k=>$v)
            $xml->addAttribute($k, $v);
    }
}