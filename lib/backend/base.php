<?php

namespace I18n\Backend;

use \I18n\I18n;
use \I18n\InvalidLocale;
use \I18n\MissingTranslationData;
use \I18n\UnknownFileType;

require_once('SymfonyComponents/YAML/sfYaml.php');

class Base
{
	private static $RESERVED_KEYS = array('scope', 'default', 'separator', 'resolve');
	private $initialized = false;
	private $translations = null;

	public function load_translations(array $filenames)
	{
		foreach ($filenames as $filename) {
			$this->load_file($filename);
		}
	}

	public function store_translations($locale, $data, $options = array())
	{
		$this->merge_translations($locale, $data, $options);
	}

	public function translate($locale, $key, $options = array())
	{
		if ($locale === null) {
			throw new InvalidLocale($locale);
		}

		if (is_array($key)) {
			foreach ($key as $k) {
				$to_translate[] = $this->translate($locale, $k, $options);
			}
			return $to_translate;
		}

		if (empty($options)) {
			$entry = $this->resolve($locale, $key, $this->lookup($locale, $key), $options);
			if ($entry === null) {
				throw new MissingTranslationData($locale, $key, $options);
			}
		} else {
			$count = isset($options['count']) ? $options['count'] : '';
			$scope = isset($options['scope']) ? $options['scope'] : '';
			$default = isset($options['default']) ? $options['default'] : '';
			$values = array_diff($options, self::$RESERVED_KEYS);

			$entry = $this->lookup($locale, $key, $scope, $options);
			if ($entry === null) {
				$entry = $default ? $this->_default($locale, $key, $default, $options) : $this->resolve($locale, $key, $entry, $options);
			}
			if ($entry === null) {
				throw new MissingTranslationData($locale, $key, $options);
			}

			if ($count) {
				$entry = $this->pluralize($locale, $entry, $count);
			}
			if ($values) {
				$entry = $this->interpolate($locale, $entry, $values);
			}
		}

		return $entry;
	}

	public function localize($locale, $object, $format = 'DEFAULT', $options = array())
	{

	}

	public function is_initialized()
	{
		return $this->initialized;
	}

	public function available_locales()
	{
		if (!$this->initialized) {
			$this->init_translations();
		}
		if (!empty($this->translations)) {
			return array_keys($this->translations);
		} else {
			return array();
		}
	}

	public function reload()
	{
		$this->initialized = false;
		$this->translations = null;
	}

	private function init_translations()
	{
		self::load_translations(\I18n\array_flatten(I18n::get_load_path()));
		$this->initialized = true;
	}

	private function translations()
	{
		if ($this->translations === null) {
			$this->translations = array();
		}
		return $this->translations;
	}

	private function lookup($locale, $key, $scope = array(), $options = array())
	{
		if ($key === null) {
			return;
		}
		if (!$this->initialized) {
			$this->init_translations();
		}
		$keys = I18n::normalize_keys($locale, $key, $scope, $options);
		$x = $this->translations;
		$result = null;
		while ($x !== null && !empty($keys)) {
			$keyToFind = array_shift($keys);
			if (!array_key_exists($keyToFind, $x)) {
				return null;
			}
			$x = $x[$keyToFind];
			if (!is_array($x)) {
				$result = $x;
				break;
			}
		}

		return $result;
		// key.inject
	}

	private function _default($locale, $object, $subject, $options = array())
	{

	}

	private function resolve($locale, $object, $subject, $options = null)
	{
		// if ($options['resolve'] === false)
		// 	return $subject;
		// switch($subject) {
		// 	// ?
		// }
		return $subject;
	}

	private function pluralize($locale, $entry, $count)
	{
		return $entry;
	}

	private function interpolate($locale, $string, $values = array())
	{
		if (!is_string($string) && empty($values)) {
			return $string;
		}

		foreach ($values as $key => $value) {
			$keys[] = '{{' . $key . '}}';
			$vals[] = $value;
		}

		return str_replace($keys, $vals, $string);
	}

	private function preserve_encoding($string)
	{

	}

	public function interpolate_lambda($object, $string, $key)
	{

	}

	private function load_file($filename)
	{
		$extension = end(explode('.', $filename));
		$method_name = 'load_' . $extension;
		if (!method_exists($this, $method_name)) {
			throw new UnknownFileType($extension, $filename);
		}

		$data = $this->$method_name($filename);
		foreach ($data as $locale => $d) {
			$this->merge_translations($locale, $d);
		}
	}

	private function load_php($filename)
	{
		require_once($filename);
		return $language;
	}

	private function load_yml($filename)
	{
		return \sfYaml::load($filename);
	}

	private function merge_translations($locale, $data, $options = array())
	{
		$this->translations[$locale] = $data;
	}
}

?>