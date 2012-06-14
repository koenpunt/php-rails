<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
	die('PHPRails requires PHP 5.3 or higher');
	
define('PHP_RAILS_VERSION_ID','0.2');

if (!defined('PHP_RAILS_AUTOLOAD_PREPEND'))
	define('PHP_RAILS_AUTOLOAD_PREPEND',true);

if(!defined('PS'))
	define('PS', PATH_SEPARATOR);
if(!defined('DS'))
	define('DS', PATH_SEPARATOR);

# Add lib directories of different sections to include path

require __DIR__ . DS . 'utils.php';

if (!defined('PHP_RAILS_AUTOLOAD_DISABLE'))
	spl_autoload_register('phprails_autoload', false, PHP_RAILS_AUTOLOAD_PREPEND);

$phprails_classmap = array();

function phprails_autoload($className){
	global $phprails_classmap;
	$mapClassName = strtr('\\', '_', $className);
	if(isset($phprails_classmap[$mapClassName]))
		return require_once $phprails_classmap[$mapClassName];
	
	$source_dirs = array(
		__DIR__ . DS . 'activesupport' . DS . 'lib' . DS,
		__DIR__ . DS . 'actionpack' . DS . 'lib' . DS
	);
	
	if (($namespaces = get_namespaces($className))){
		$className = array_pop($namespaces);
		$directories = array();

		foreach ($namespaces as $directory)
			$directories[] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $directory));
	
		$path .= implode($directories, DS) . DS;
		foreach($source_dirs as $source_dir){
			$file = "{$source_dir}{$path}{$className}.php";
			if(file_exists($file))
				$phprails_classmap[$mapClassName] = $file;
				return require_once $file;
				break;
		}
	}
}
