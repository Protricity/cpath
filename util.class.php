<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;


// TODO split this class up into generic methods and request-oriented methods
use CPath\Interfaces\IRequest;
use CPath\Request\CLI;
use CPath\Request\Web;

/**
 * Class Util provides information about the current request
 * @package CPath
 */
abstract class Util {

    /**
     * Prepare an object for json serialization
     * @param $object mixed the object to serialize
     * @param array|null $JSON the existing json data to add to
     * @return array|bool|float|int|null|string the json data to serialize
     */
    public static function toJSON($object, &$JSON=NULL) {
        if($JSON == NULL) {
            $JSON = array();
        }

        if(is_scalar($object)) {
            $JSON = $object;
        } elseif($object instanceof Interfaces\IJSON) {
            $object->toJSON($JSON);
        } elseif(is_array($object) || $object instanceof \Traversable) {
            foreach($object as $key=>$val) {
                if(!isset($JSON[$key])) $JSON[$key] = array();
                self::toJSON($val, $JSON[$key]);
            }
        } else {
            $JSON = $object;
        }
        return $JSON;

    }

    /**
     * Prepare an object for xml serialization
     * @param $object mixed the object to serialize
     * @param \SimpleXMLElement|string $root the existing xml instance or root tag to use for a new xml instance
     * @return \SimpleXMLElement the xml instance with serialized data added in
     */
    public static function toXML($object, $root='root') {

        if(!($root instanceof \SimpleXMLElement)) {
            $root = new \SimpleXMLElement("<?xml version=\"1.0\"?><{$root}></{$root}>");
        }

        if($object instanceof Interfaces\IXML) {
            $object->toXML($root);
            return $root;
        }

        foreach($object as $key=>$val) {
            if(is_int($key)) $key = 'item';
            if($val instanceof Interfaces\IXML) {
                $key = strtolower(basename(get_class($val)));
                $ch = $root->addChild($key);
                $val->toXML($ch);
            } elseif(is_array($val)) {
                $ch = $root->addChild($key);
                self::toXML($val, $ch);
            } else {
                $root->addChild($key, $val);
            }
        }
        return $root;
    }
}
