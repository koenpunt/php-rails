<?php

namespace I18n\Backend;

use \I18n\I18n;
use \I18n\InvalidLocale;
use \I18n\MissingTranslationData;
use \I18n\UnknownFileType;

require_once('SymfonyComponents/YAML/sfYaml.php');

class Base
{
	private static $RESERVED_KEYS = array('scope' => null, 'default' => null, 'separator' => null, 'resolve' => null);
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
			$count = isset($options['count']) ? $options['count'] : null;
			$scope = isset($options['scope']) ? $options['scope'] : null;
			$default = isset($options['default']) ? $options['default'] : null;
			$values = array_diff_key($options, self::$RESERVED_KEYS);

			$entry = $this->lookup($locale, $key, $scope, $options);
			if ($entry === null) {
				$entry = $default ? $this->_default($locale, $key, $default, $options) : $this->resolve($locale, $key, $entry, $options);
				// $entry = $default ? $this->_default($locale, $key, $default, $options) : null;
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
		return array_keys($this->translations());
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
		$separator = isset($options['separator']) ? $options['separator'] : null;
		$keys = I18n::normalize_keys($locale, $key, $scope, $separator);
		$result = $this->translations();
		while ($result !== null && !empty($keys)) {
			$key = new Symbol(array_shift($keys));
			if (!array_key_exists($key->get_value(), $result)) {
				return null;
			}
			$result = $result[$key->get_value()];
			if ($result instanceof Symbol) {
				$result = $this->resolve($locale, $key, $result, $options);
			}
		}
		return $result;
	}

	private function _default($locale, $object, $subject, $options = array())
	{
		unset($options['default']);
		if (!is_array($subject)) {
			return $this->resolve($locale, $object, $subject, $options);
		}

		foreach ($subject as $item) {
			$item = new Symbol($item);
			$result = $this->resolve($locale, $object, $item, $options);
			if ($result !== null) {
				return $result;
			}
		}
		return null;
	}

	private function resolve($locale, $object, $subject, $options = null)
	{
		if (isset($options['resolve']) && $options['resolve'] === false) {
			return $subject;
		}

		try {
			if ($subject instanceof Symbol) {
				$options['locale'] = $locale;
				$options['raise'] = true;
				return I18n::translate($subject->get_value(), $options);
			} else {
				return $subject;
			}
		} catch (MissingTranslationData $exception) {
			return null;
		}
	}

	private function pluralize($locale, $entry, $count)
	{
		// if ($count === 0) {
		// 	$key = 'zero';
		// }
		// $key = isset($key) ? $key : ($count === 1 ? 'one' : 'other');
		// if ($entry) {
		// 	throw new InvalidPluralizationData($entry, $count);
		// }
		return $entry;
	}

	private function interpolate($locale, $string, $values = array())
	{
		if (!is_string($string) || empty($values)) {
			return $string;
		}

		foreach ($values as $key => $value) {
			if (is_array($value)) {
				continue;
			}
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
		if (isset($this->translations[$locale])) {
			$this->translations[$locale] = array_merge_recursive($this->translations[$locale], $data);
		} else {
			$this->translations[$locale] = $data;
		}
	}
}

class Symbol
{
	private $value;

	public function __construct($value = null)
	{
		$this->set_value($value);
	}

	public function get_value()
	{
		return $this->value;
	}

	public function set_value($value = null)
	{
		if ($value instanceof Symbol) {
			$value = $value->get_value();
		}
		$this->value = $value;
	}
}

?>