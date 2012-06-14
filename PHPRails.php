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
set_include_path(
	get_include_path() . PS . 
	__DIR__ . DS . 'activesupport' . DS . 'lib' . PS .
	__DIR__ . DS . 'actionpack' . DS . 'lib'
);

require __DIR__ . DS . 'utils.php';

if (!defined('PHP_RAILS_AUTOLOAD_DISABLE'))
	spl_autoload_register('phprails_autoload', false, PHP_RAILS_AUTOLOAD_PREPEND);

function phprails_autoload($className){
	if (($namespaces = get_namespaces($className))){
		$path = __DIR__;
		$className = array_pop($namespaces);
		$directories = array();

		foreach ($namespaces as $directory)
			$directories[] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $directory));
	
		$path .= implode($directories, DS) . DS;
		$file = "{$path}{$className}.php";
		if(stream_resolve_include_path($file))
			require_once $file;
		}
	}
}
