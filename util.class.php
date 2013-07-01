<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;


// TODO split this class up into generic methods and request-oriented methods
/**
 * Class Util provides information about the current request
 * @package CPath
 */
abstract class Util {

    private static $mHeaders = null;
    private static $mUrl = array();

    /**
     * Initialize the static class and parses request information
     */
    public static function init() {
        if(!empty($_SERVER["REQUEST_URI"])) {
            self::$mUrl = parse_url($_SERVER['REQUEST_URI']);
            self::$mUrl['method'] = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : 'GET';
        } elseif ($args = $_SERVER['argv']) {
            array_shift($args);
            if(sizeof($args) > 1) self::$mUrl = parse_url($args[1]);
            else self::$mUrl = array('path'=>'');
            if($args) self::$mUrl['method'] = $args[0];
            self::$mUrl['args'] = $args;
            if(isset(self::$mUrl['query'])) parse_str(self::$mUrl['query'], $_GET);
            // TODO: $_POST
        }
        $root = dirname($_SERVER['SCRIPT_NAME']);
        $request = self::$mUrl["path"];
        if(stripos($request, $root) === 0)
            $request = substr($request, strlen($root));
        self::$mUrl['route'] = self::$mUrl['method'] . " " . $request;

        self::$mHeaders = function_exists('getallheaders')
            ? getallheaders()
            : array('Accept'=> isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'text/plain');
    }

    /**
     * Get a request header
     * @param $name string the header key
     * @return string|null the header value or null if it was not found
     */
    public static function getHeader($name) {
        return isset(self::$mHeaders[$name]) ? self::$mHeaders[$name] : NULL;
    }

    /**
     * Get path information for the request url
     * @param string|null $key if set, return only the data that coorisponds to this vaue
     * @return array|string the url data
     */
    public static function getUrl($key=NULL) {
        if($key !== NULL)
            return self::$mUrl[$key];
        return self::$mUrl;
    }

    /**
     * Determines all accepted mimetypes from the request. Narrows different types into the most common mimetype
     * @return array a list of accepted mimetypes
     */
    public static function getAcceptedTypes() {
        static $types = NULL;
        if($types === NULL) {
            $types = self::getHeader('Accept');
            $types = explode(',', strtolower($types));
            foreach($types as $i=>$type) {
                list($type) = explode(';', $type);
                switch ($type) {
                    case 'application/json':
                    case 'application/x-javascript':
                    case 'text/javascript':
                    case 'text/x-javascript':
                    case 'text/x-json':
                        $types[$i] = 'application/json';
                        break;
                    case 'application/xml':
                    case 'text/xml':
                        $types[$i] = 'application/xml';
                        break;
                    case 'text/html':
                    case 'application/xhtml+xml':
                        $types[$i] = 'text/html';
                        break;
                    case 'text/plain':
                        $types[$i] = 'text/plain';
                        break;
                }
            }
            $types = array_unique($types);
        }
        return $types;
    }

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
            if(is_int($key)) $key = 'item'.$key;
            if(is_array($val)) {
                $ch = $root->addChild($key);
                self::toXML($val, $ch);
            } else {
                $root->addChild($key, $val);
            }
        }
        return $root;
    }
}
// Init this class on load
Util::init();
