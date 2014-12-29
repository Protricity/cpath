<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 2:59 PM
 */
namespace CPath;

const AUTOLOADER = true;

class Autoloader
{
	/** @var Array */
	private static $mLoaders = array();

	/**
	 * @param String $namespace with trailing backslash
	 * @param String|Callable $path
	 */
	public static function addLoader($namespace, $path) {
		if(is_string($path))
			$path = rtrim($path, '\\/') . '/';
		$namespace = str_replace('\\', '/', $namespace);
		self::$mLoaders[$namespace] = $path;
	}

	public static function getLoaderPaths() {
		return self::$mLoaders;
	}

	/**
	 * @param $className
	 * @return String class autoloader path
	 */
	public static function getPathFromClassName($className) {
		foreach (self::$mLoaders as $prefix => $path) {
			if (stripos($className, $prefix) === 0) {
				if(is_callable($path))
					$path = $path($className);
				else
					$path = $path . substr($className, strlen($prefix) + 1) . '.php';
				return $path;
			}
		}
		throw new \InvalidArgumentException("Class did not match autoloader prefix: ". $className);
	}

	/**
	 * Autoloader for CPath + registered namespaces. Path matches namespace hierarchy of Class
	 * @param $className
	 */
	static function loadClass($className) {
		foreach (self::$mLoaders as $prefix => $path) {
			if (stripos($className, $prefix) === 0) {
				if(is_callable($path))
					$path = $path($className);
				else
					$path = $path . substr(str_replace('\\', '/', $className), strlen($prefix) + 1) . '.php';
				include($path);
				return;
			}
		}
	}

}
Autoloader::addLoader(__NAMESPACE__, __DIR__);
spl_autoload_register(__NAMESPACE__ . '\Autoloader::loadClass', true);




