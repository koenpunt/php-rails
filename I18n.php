<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	die('PHP I18n requires PHP 5.3 or higher');
}

spl_autoload_register('i18n_autoload');

function i18n_autoload($class_name)
{

}

class I18n
{
	private $backend = null;
	private $load_path = null;
	private $default_locale = 'en';
	private $default_separator = '.';
	private $exception_handler = null;

	public function get_backend()
	{
		return $this->backend;
	}

	public function set_backend($backend)
	{
		$this->backend = $backend;
	}

	public function get_default_locale()
	{
		return $this->default_locale;
	}

	public function set_default_locale($locale)
	{
		$this->default_locale = $locale;
	}

	public function get_locale()
	{

	}

	public function set_locale($locale)
	{

	}

	public function get_available_locales()
	{

	}

	public function set_available_locales($Locale)
	{

	}

	public function get_default_separator()
	{
		return $this->default_separator;
	}

	public function set_default_separator($separator)
	{
		$this->default_separator = $separator;
	}

	public function set_exception_handler($exception_handler)
	{
		$this->exception_handler = $exception_handler;
	}

	public function get_load_path()
	{
		return $this->load_path;
	}

	public function set_load_path($load_path)
	{
		$this->load_path = $load_path;
	}

	public function translate($args)
	{
		$options = ?;
		$key = ?;
		$locale = ?;
		$raises
	}

	public function translate_exception($key, $options = array())
	{

	}

	public function localize($object, $options = array())
	{

	}

	public function normalize_keys($locale, $key, $scope, $separator = null)
	{
		$keys = array_merge($locale, $scope, $key);
	}

	private function default_exception_handler($exception, $locale, $key, $options)
	{

	}

	private function handle_exception($exception, $locale, $key, $options)
	{

	}
}

?>