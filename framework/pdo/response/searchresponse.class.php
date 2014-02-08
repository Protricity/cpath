<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Response;
use CPath\Compare\IComparable;
use CPath\Compare\IComparator;
use CPath\Compare\NotEqualException;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\Response\Types\Response;

class SearchResponse extends \CPath\Framework\Response\Types\Response {
    private $mQuery, $mTotal;

    /**
     * Create a new response
     * @param String $msg the response message
     * @param PDOSelect $Query search query
     */
    function __construct($msg=NULL, PDOSelect $Query) {
        \CPath\Framework\Response\Types\parent::__construct($msg, true, $Query);
        $this->mQuery = $Query;
    }

    function getQuery() {
        return $this->mQuery;
    }

    function toJSON(Array &$JSON) {
        parent::toJSON($JSON);
        $JSON['stats'] = array();
        $stats = $this->mQuery->getDescriptor()->execFullStats();
        $stats->toJSON($JSON['stats']);
    }

    function toXML(\SimpleXMLElement $xml) {
        parent::toXML($xml);
        $child = $xml->addChild('stats');
        $stats = $this->mQuery->getDescriptor()->execFullStats();
        $stats->toXML($child);
    }

    /**
     * Compare two instances of this object
     * @param IComparable|SearchResponse $obj the object to compare against $this
     * @param IComparator $C the IComparator instance
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareTo(IComparable $obj, IComparator $C) {
        parent::compareTo($obj, $C);
        $C->compareScalar($this->mQuery->getSQL(), $obj->mQuery->getSQL(), "Response Query");
    }
}
