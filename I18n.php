<?php

namespace I18n;

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	die('PHP I18n requires PHP 5.3 or higher');
}

require_once('lib/backend/base.php');

define('APP', dirname(__FILE__));

function array_flatten($a, $pref='') {
	$ret = array();
	foreach ($a as $i => $j) {
		if (is_array($j)) {
			$ret = array_merge($ret, array_flatten($j, $pref . $i));
		} else {
			$ret[$pref . $i] = $j;
		}
	}
	return $ret;
}

// spl_autoload_register('i18n_autoload');
//
// function i18n_autoload($class_name)
// {
// 	$path = I18n\Config::instance()->get_model_directory();
// 	$root = realpath(isset($path) ? $path : '.');
//
// 	if (($namespaces = I18n\get_namespaces($class_name)))
// 	{
// 		$class_name = array_pop($namespaces);
// 		$directories = array();
//
// 		foreach ($namespaces as $directory)
// 			$directories[] = $directory;
//
// 		$root .= DIRECTORY_SEPARATOR . implode($directories, DIRECTORY_SEPARATOR);
// 	}
//
// 	$file = "$root/$class_name.php";
//
// 	if (file_exists($file))
// 		require $file;
// }

class I18n
{
	private static $backend = null;
	private static $load_path = null;
	private static $default_locale = 'en';
	private static $default_separator = '.';
	private static $exception_handler = null;
	private static $available_locales = array();
	private static $current_locale = null;

	public function get_backend()
	{
		if (self::$backend === null)
			self::$backend = new Backend\Base();
		return self::$backend;
	}

	public function set_backend($backend)
	{
		self::$backend = $backend;
	}

	public function get_default_locale()
	{
		return self::$default_locale;
	}

	public function set_default_locale($locale)
	{
		self::$default_locale = $locale;
	}

	public function get_locale()
	{
		if (self::$current_locale === null)
			self::$current_locale = self::$default_locale;
		return self::$current_locale;
	}

	public function set_locale($locale)
	{
		self::$current_locale = $locale;
	}

	public function get_available_locales()
	{
		if (empty(self::$available_locales))
			self::$available_locales = self::$backend->available_locales();

		return self::$available_locales;
	}

	public function set_available_locales($Locale)
	{
		self::$available_locales = $locale;
	}

	public function get_default_separator()
	{
		return self::$default_separator;
	}

	public function set_default_separator($separator)
	{
		self::$default_separator = $separator;
	}

	public function set_exception_handler($exception_handler)
	{
		self::$exception_handler = $exception_handler;
	}

	public function get_load_path()
	{
		if (self::$load_path === null)
			self::$load_path = array();

		return self::$load_path;
	}

	public function set_load_path($load_path)
	{
		self::$load_path = $load_path;
	}

	public function translate($key, $options = array())
	{
		// $options = array_diff_key($args, array(0));
		// $key = $args[0];
		if (array_key_exists('locale', $options)) {
			$locale = $options['locale'];
			unset($options['locale']);
		} else {
			$locale = self::$current_locale;
		}
		if (array_key_exists('raises', $options)) {
			$raises = $options['raises'];
			unset($options['raises']);
		}
		try {
			return self::get_backend()->translate($locale, $key, $options);
		} catch (I18n\ArgumentError $exception) {
			if ($raises)
				throw $exception;

			self::handle_exception($exception, $locale, $key, $options);
		}
	}

	public function translate_exception($key, $options = array())
	{

	}

	public function localize($object, $options = array())
	{

	}

	public function normalize_keys($locale, $key, $scope, $separator = null)
	{
		// $keys = array_merge(array($locale), array($scope), array($key));
		if ($locale)
			$keys[] = explode(self::$default_separator, $locale);
		if ($scope)
			$keys[] = explode(self::$default_separator, $scope);
		if ($key)
			$keys[] = explode(self::$default_separator, $key);
		// if any value is a dot key, split
		// return implode(self::$default_separator, $keys);
		$keys = array_flatten($keys);

		return $keys;
	}

	private function default_exception_handler($exception, $locale, $key, $options)
	{

	}

	private function handle_exception($exception, $locale, $key, $options)
	{

	}
}

?>