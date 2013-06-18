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
    private static $mUrl = array();

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
            if(self::$mUrl['query']) parse_str(self::$mUrl['query'], $_GET);
            // TODO: $_POST
        }
        $root = dirname($_SERVER['SCRIPT_NAME']);
        $request = self::$mUrl["path"];
        if(stripos($request, $root) === 0)
            $request = substr($request, strlen($root));
        self::$mUrl['route'] = self::$mUrl['method'] . " " . $request;

    }

    public static function getHeader($name) {
        static $headers = NULL;
        if($headers === null)
            $headers = function_exists('getallheaders') ? getallheaders() : array('Accept'=>'text/plain');

        return $headers[$name];
    }

    public static function getUrl($key=NULL) {
        if($key !== NULL)
            return self::$mUrl[$key];
        return self::$mUrl;
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
Util::init();