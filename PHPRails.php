<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
	die('PHPRails requires PHP 5.3 or higher');
	
define('PHP_RAILS_VERSION_ID','0.2');

#if (!defined('PHP_RAILS_AUTOLOAD_PREPEND'))
#	define('PHP_RAILS_AUTOLOAD_PREPEND',true);

if(!defined('PS'))
	define('PS', PATH_SEPARATOR);
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

# Add lib directories of different sections to include path

require __DIR__ . DS . 'utils.php';

#if (!defined('PHP_RAILS_AUTOLOAD_DISABLE')){
#	spl_autoload_register(array('PHPRails', 'load'), false, PHP_RAILS_AUTOLOAD_PREPEND);
#}

class PHPRails{
	
	static $_classMap = array();
	
	static $_map = array();
	
	static $_packages = array();
	
	public static function init(){
		self::$_packages = array(
			'active_support' => array(__DIR__ . DS . 'activesupport' . DS . 'lib'),
			'action_pack' => array(__DIR__ . DS . 'actionpack' . DS . 'lib'),
			'action_view' => array(__DIR__ . DS . 'actionpack' . DS . 'lib'),
			'ruby' => array(__DIR__ . DS . 'ruby' . DS . 'lib') ,
		);
	}
	
	public static function import($path){
		$paths = self::path($path);

		foreach($paths as $_path){
			$file = $_path . DS . $path . '.php';
			if(file_exists($file)){
				return require_once $file;
			}
		}
		throw new Exception("LoadError: cannot load such file -- {$path}");
	}
	
	public static function path($location) {
		if($slash = strpos($location, '/')){
			$type = substr($location, 0, $slash);
		}else{
			$type = $location;
		}
		if (!isset(self::$_packages[$type])) {
			return array($location);
		}
		return self::$_packages[$type]; // . DS . $location;
		return array_map(function($package) use ($location){
			$package . DS . $location;
		}, self::$_packages[$type]);
	}
	
	protected static function _map($path, $file) {
		self::$_map[$path] = $file;
	}
	
	/**
	 * Returns a file's complete path.
	 *
	 * @param string $name unique name
	 * @return mixed file path if found, false otherwise
	 */
	protected static function _mapped($name) {
		return isset(self::$_map[$name]) ? self::$_map[$name] : false;
	}
	
	protected static function _get_namespaces($className){
		if( self::_is_namespaced($className) ){
			return explode('\\', $className);
		}
	}
	
	protected static function _is_namespaced($className){
		return (strpos($className, '\\') !== false);
	}
}