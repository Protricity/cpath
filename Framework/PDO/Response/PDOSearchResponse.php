<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Response;
use CPath\Describable\Describable;
use CPath\Framework\Data\Compare\IComparable;
use CPath\Framework\Data\Compare\Util\CompareUtil;
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
     * @return PDOSelect
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
     * Compare two objects
     * @param IComparable $obj the object to compare against $this
     * @return integer < 0 if $obj is less than $this; > 0 if $obj is greater than $this, and 0 if they are equal.
     */
    function compareTo(IComparable $obj)
    {
        if(!$obj instanceof PDOSearchResponse)
            return 1;

        $Util = new CompareUtil();
        return $Util->compareScalar(
            $this->mQuery->getSQL(),
            $obj->mQuery->getSQL()
        );
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
