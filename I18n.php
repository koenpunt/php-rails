<?php

namespace I18n;

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	die('PHP I18n requires PHP 5.3 or higher');
}

define('APP', dirname(__FILE__));

require_once('lib/exceptions.php');
require_once('lib/backend/base.php');
require_once('lib/symbol.php');
require_once('lib/utils.php');

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
		if (self::$backend === null) {
			self::$backend = new Backend\Base();
		}
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
		if (self::$current_locale === null) {
			self::$current_locale = self::$default_locale;
		}
		return self::$current_locale;
	}

	public function set_locale($locale)
	{
		self::$current_locale = $locale;
	}

	public function get_available_locales()
	{
		if (empty(self::$available_locales)) {
			self::$available_locales = self::$backend->available_locales();
		}
		return self::$available_locales;
	}

	public function set_available_locales($locale)
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
		if (self::$load_path === null) {
			self::$load_path = array();
		}
		return self::$load_path;
	}

	public function set_load_path($load_path)
	{
		self::$load_path = $load_path;
	}

	public function push_load_path($load_path)
	{
		if (count(self::$load_path) === 0 || !in_array($load_path, self::$load_path)) {
			self::$load_path[] = $load_path;
		}
	}

	public function translate($key, $options = array())
	{
		if ($key === null) {
			return null;
		}
		// $options = array_diff_key($args, array(0));
		// $key = $args[0];
		if (array_key_exists('locale', $options)) {
			$locale = $options['locale'];
			unset($options['locale']);
		} else {
			$locale = self::get_locale();
		}
		$raises = false;
		if (array_key_exists('raise', $options)) {
			$raises = $options['raise'];
			unset($options['raise']);
		}
		try {
			return self::get_backend()->translate($locale, $key, $options);
		} catch (\InvalidArgumentException $exception) {
			if ($raises) {
				throw $exception;
			}
			self::handle_exception($exception, $locale, $key, $options);
		}
	}

	public function translate_exception($key, $options = array())
	{
		$options['raise'] = true;
		return self::translate($key, $options);
	}

	public function localize($object, $options = array())
	{

	}

	public function normalize_keys($locale, $key, $scope, $separator = null)
	{
		// $keys = array_merge(array($locale), array($scope), array($key));
		if ($locale) {
			$keys[] = explode(self::$default_separator, $locale);
		}
		if ($scope) {
			if (is_array($scope)) {
				$keys[] = $scope;
			} else {
				$keys[] = explode(self::$default_separator, $scope);
			}
		}
		if ($key) {
			if ($key instanceof Symbol) {
				$key = $key->get_value();
			}

			$keys[] = explode(self::$default_separator, $key);
		}
		// if any value is a dot key, split
		// return implode(self::$default_separator, $keys);
		$keys = array_flatten($keys);
		return $keys;
	}

	private function default_exception_handler($exception, $locale, $key, $options)
	{
		if (is_a($exception, 'MissingTranslationData')) {
			return $exception->getMessage();
		}
		throw $exception;
	}

	private function handle_exception($exception, $locale, $key, $options)
	{

	}
}

?>