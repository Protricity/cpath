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
use CPath\Describable\Describable;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\Response\Types\AbstractResponse;

class PDOSearchResponse extends AbstractResponse implements IMappable, IComparable {
    private $mQuery;

    /**
     * Create a new response
     * @param PDOSelect $Query search query
     * @param String|Null $message the response message
     */
    function __construct(PDOSelect $Query, $message=null) {
        parent::__construct($message, true);
        $this->mQuery = $Query;
    }

    /**
     * Return the PDOSelect query instance
     * @return PDOSearchResponse
     */
    function getQuery() {
        return $this->mQuery;
    }

    /**
     * @return String
     * @override
     */
    function getMessage() {
        return parent::getMessage() ?: Describable::get($this->mQuery)->getTitle();
    }


    /**
     * Compare two instances of this object
     * @param IComparable|PDOSearchResponse $obj the object to compare against $this
     * @param IComparator $C the IComparator instance
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareTo(IComparable $obj, IComparator $C) {
        $C->compareScalar($this->mQuery->getSQL(), $obj->mQuery->getSQL(), "DataResponse Query");
    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map) {
        $Map->mapDataToKey('stats', $this->mQuery->getDescriptor()->execFullStats());
        $Map->mapDataToKey('results', $this->mQuery);
    }
}
