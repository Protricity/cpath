<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;


class Util {

    private static $mHeaders = null;

    public static function getHeader($name) {
        static $headers = NULL;
        if($headers === null)
            $headers = getallheaders();

        return $headers[$name];
    }

    public static function parseUrl($key=NULL) {
        static $parseUrl = NULL;
        if($parseUrl === null)
            $parseUrl = parse_url($_SERVER["REQUEST_URI"]);

        if($key !== NULL)
            return $parseUrl[$key];
        return $parseUrl;
    }

    public static function getUrlRoute() {
        static $route = NULL;
        if($route === NULL) {
            $root = dirname($_SERVER['SCRIPT_NAME']);
            $request = self::parseUrl("path");
            if(stripos($request, $root) === 0)
                $request = substr($request, strlen($root));
            $route = $_SERVER["REQUEST_METHOD"] . " " . $request;
        }
        return $route;
    }

    public static function getAcceptedTypes() {
        static $types = NULL;
        if($types === NULL) {
            $types = Util::getHeader('Accept');
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
                }
            }
            $types = array_unique($types);
        }
        return $types;
    }

    public static function toJSON($object, &$JSON=NULL) {
        if($JSON == NULL) {
            $JSON = array();
        }

        if($object instanceof Interfaces\IJSON) {
            $object->toJSON($JSON);
            return $JSON;
        }

        foreach($object as $key=>$val) {
            if(is_array($val)) {
                if(!isset($JSON[$key])) $JSON[$key] = array();
                self::toJSON($val, $JSON[$key]);
            } else {
                $JSON[$key] = $val;
            }
        }
        return $JSON;
    }

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