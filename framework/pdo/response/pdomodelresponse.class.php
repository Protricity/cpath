<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Response;
use CPath\Describable\Describable;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\Response\Types\AbstractResponse;

class PDOModelResponse extends AbstractResponse implements IMappable {
    private $mModel;

    /**
     * Create a new response
     * @param PDOModel $Model model instance
     * @param String|Null $message the response message
     */
    function __construct(PDOModel $Model, $message=NULL) {
        parent::__construct($message, true);
        $this->mModel = $Model;
    }

    /**
     * @return String
     * @override
     */
    function getMessage() {
        return parent::getMessage() ?: Describable::get($this->mModel)->getTitle();
    }

    /**
     * Return the model instance
     * @return PDOModel
     */
    function getModel() {
        return $this->mModel;
    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map) {
        $Map->mapDataToKey('model', $this->mModel);
    }
}
