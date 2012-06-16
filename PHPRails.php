<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
	die('PHPRails requires PHP 5.3 or higher');
	
define('PHP_RAILS_VERSION_ID','0.2');

if (!defined('PHP_RAILS_AUTOLOAD_PREPEND'))
	define('PHP_RAILS_AUTOLOAD_PREPEND',true);

if(!defined('PS'))
	define('PS', PATH_SEPARATOR);
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

# Add lib directories of different sections to include path

require __DIR__ . DS . 'utils.php';

if (!defined('PHP_RAILS_AUTOLOAD_DISABLE')){
	spl_autoload_register(array('PHPRails', 'load'), false, PHP_RAILS_AUTOLOAD_PREPEND);
}
#PHPRails::uses('action_view/helpers/tag_helper');
class PHPRails{
	
	static $current = array();
	
	static $_classMap = array();
	
	static $_map = array();
	
	static $_packages = array();
	
	public static function init(){
		self::$_packages = array(
			'active_support' => __DIR__ . DS . 'activesupport' . DS . 'lib',
			'action_pack' => __DIR__ . DS . 'actionpack' . DS . 'lib',
			'helper' => __DIR__ . DS . 'actionpack' . DS . 'lib' . DS . 'action_view' . DS . 'helpers',
			'ruby' => __DIR__ . DS . 'ruby' . DS . 'lib' ,
		);
	}
	
	public static function load($className){
		$mapClassName = str_replace('\\', '-', $className);
		
		if( !isset(self::$_classMap[$mapClassName]) ){
			return false;
		}
		
		$package = self::$_classMap[$mapClassName];
			
		if( $file = self::_mapped($mapClassName) ){
			return include $file;
		}
		
		$path = self::path($package);
		#var_dump(self::$_classMap, $path);
		if( $namespaces = self::_get_namespaces($className) ){
			$className = array_pop($namespaces);
			#var_dump($className);
			$file = $path . DS . $className . '.php';
			if(file_exists($file)){
				self::_map($file, $mapClassName);
				return include $file;
			}
		}
	}
	
	public static function uses($class_or_path_with_class, $path = null){
		if(is_null($path)){
			$last_slash = strrpos($class_or_path_with_class, '/');
			$path = $last_slash ? substr($class_or_path_with_class, 0, $last_slash) : $class_or_path_with_class;
			$class = str_replace(' ', '', ucwords(str_replace('_', ' ', ( $last_slash ? substr($class_or_path_with_class, $last_slash + 1) : $class_or_path_with_class ))));
		}else{
			$class = $class_or_path_with_class;
			
		}
		$mapClassName = preg_replace_callback('/(\/)?(\w+)/', function($match){
			return ($match[1] ? '-' : '') . preg_replace('/([a-z]+)_?/e', "ucfirst('\\1')", $match[2]);
		} , $path);
		
		self::$_classMap[$mapClassName . '-' . $class] = $path;
	}
	
	/**
	 * Used to read information stored path
	 *
	 * Usage:
	 *
	 * `App::path('Model'); will return all paths for models`
	 *
	 * `App::path('Model/Datasource', 'MyPlugin'); will return the path for datasources under the 'MyPlugin' plugin`
	 *
	 * @param string $type type of path
	 * @param string $plugin name of plugin
	 * @return array
	 * @link http://book.cakephp.org/2.0/en/core-utility-libraries/app.html#App::path
	 */
	public static function path($location) {
		if($slash = strpos($location, '/')){
			$type = substr($location, 0, $slash);
		}else{
			$type = $location;
		}
		if (!isset(self::$_packages[$type])) {
			return array($location);
		}
		return self::$_packages[$type] . DS . $location;
		return array_map(function($package) use ($location){
			self::$_packages[$type] . DS . $location;
		}, self::$_packages[$type]);
	}
	
	/**
	 * Maps the $name to the $file.
	 *
	 * @param string $file full path to file
	 * @param string $name unique name for this map
	 * @param string $plugin camelized if object is from a plugin, the name of the plugin
	 * @return void
	 */
	protected static function _map($file, $name) {
		self::$_map[$name] = $file;
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

PHPRails::init();