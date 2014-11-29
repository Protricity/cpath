<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Data\Describable;

class Description implements IDescribable {
    private $mDesc = null;

	/**
	 * @param string|object|IDescribable $describable
	 */
	function __construct($describable) {
        if(!is_object($describable)) {
            $this->mDesc = htmlentities((String) $describable);

        } else if ($describable instanceof IDescribable) {
	        $this->mDesc = $describable->getDescription();

        } else {
            if(method_exists($describable, '__toString')) {
                $this->mDesc = htmlentities((String) $describable);
            } else {
                $this->mDesc = get_class($describable);
            }
        }

    }

    /**
     * Get the Object Description
     * @return String description for this Object
     */
    function getDescription() { return $this->mDesc; }

    /**
     * Set the Object Description
     * @param String $desc the new description
     * @return $this
     */
    function setDescription($desc) { $this->mDesc = $desc; return $this; }

	function __toString() {
		return $this->getDescription();
	}


}