<?php

namespace I18n\Backend;

use \I18n\I18n;
use \I18n\InvalidLocale;
use \I18n\InvalidPluralizationData;
use \I18n\MissingTranslation;
use \I18n\MissingInterpolationArgument;
use \I18n\ReservedInterpolationKey;
use \I18n\UnknownFileType;
use \I18n\Symbol;

require_once('SymfonyComponents/YAML/sfYaml.php');

class Base
{
	static $RESERVED_KEYS = array('scope', 'default', 'separator', 'resolve', 'object', 'fallback', 'format', 'cascade', 'throw', 'raise', 'rescue_format');
	#RESERVED_KEYS_PATTERN = /%\{(#{RESERVED_KEYS.join("|")})\}/
	
	private $initialized = false;
	private $translations = array();

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
		if (is_null($locale)){
			throw new InvalidLocale($locale);
		}
		$entry = $key ? self::lookup($locale, $key, get($options, 'scope'), $options) : null;

		if( empty($options)){
			$entry = self::resolve($locale, $key, $entry, $options);
		}else{
			list($count, $default) = array(get($options, 'count'), get($options, 'default'));
			$values = array_diff_key($options, array_flip(self::$RESERVED_KEYS));

			if(is_null($entry)){
				$entry = $default ? $this->_default($locale, $key, $default, $options) : $this->resolve($locale, $key, $entry, $options);
			}
		}
		if(is_null($entry)){
			throw new MissingTranslation($locale, $key, $options);
		}
		#entry = entry.dup if entry.is_a?(String)

		if(isset($count)){
			$entry = self::pluralize($locale, $entry, $count);
		}
		if (isset($values)){
			$entry = self::interpolate($locale, $entry, $values);
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
		return array_keys($this->translations);
	}

	public function reload()
	{
		$this->initialized = false;
		$this->translations = array();
	}

	private function init_translations()
	{
		self::load_translations(array_flatten(I18n::get_load_path()));
		$this->initialized = true;
	}

	# Looks up a translation from the translations hash. Returns nil if
	# eiher key is nil, or locale, scope or key do not exist as a key in the
	# nested translations hash. Splits keys or scopes containing dots
	# into multiple keys, i.e. <tt>currency.format</tt> is regarded the same as
	# <tt>%w(currency format)</tt>.
	protected function lookup($locale, $key, $scope = array(), $options = array()){
		/*
        init_translations unless initialized?
        keys = I18n.normalize_keys(locale, key, scope, options[:separator])

        keys.inject(translations) do |result, _key|
          _key = _key.to_sym
          return nil unless result.is_a?(Hash) && result.has_key?(_key)
          result = result[_key]
          result = resolve(locale, _key, result, options.merge(:scope => nil)) if result.is_a?(Symbol)
          result
        end
		*/
		if (!$this->initialized) {
			$this->init_translations();
		}
		$keys = I18n::normalize_keys($locale, $key, $scope, get($options, 'separator'));
		return array_reduce($keys, function($result, $_key) use ($locale, $options){
			$_key = to_sym($_key);
			if( !(is_hash($result) && array_key_exists((string)$_key, $result) ) ){
				return null;
			} 
			$result = $result[(string)$_key];
			if ($result instanceof Symbol) {
				$result = $this->resolve($locale, $_key, $result, $options);
			}
			return $result;
		}, $this->translations);
	}

	private function _default($locale, $object, $subject, $options = array())
	{
		unset($options['default']);
		if (!is_array($subject)) {
			return $this->resolve($locale, $object, $subject, $options);
		}

		foreach ($subject as $item) {
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
		} catch (MissingTranslation $exception) {
			return null;
		}
	}
	
	# Picks a translation from an array according to English pluralization
	# rules. It will pick the first translation if count is not equal to 1
	# and the second translation if it is equal to 1. Other backends can
	# implement more flexible or complex pluralization rules.
	private function pluralize($locale, $entry, $count){
		if( !(is_hash($entry) && $count)){
			return $entry;
		}
		
		if( $count == 0 && array_key_exists('zero', $entry)){
			 $key = 'zero';
		}
		$key = isset($key) ? $key : ( $count == 1 ? 'one' : 'other' );
		if(!array_key_exists($key, $entry)){
			throw new InvalidPluralizationData($entry, $count);
		}
		return $entry[$key];
	}
	
	# Interpolates values into a given string.
	#
	#   interpolate "file %{file} opened by %%{user}", :file => 'test.txt', :user => 'Mr. X'
	#   # => "file test.txt opened by %{user}"
	protected function interpolate($locale, $string, $values = array()){
		if( is_string($string) && !empty($values)){
			return I18n::interpolate($string, $values);
		}
		return $string;
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
		require($filename);
		if (!isset($language)) {
			return array();
		}
		return $language;
	}

	private function load_yml($filename)
	{
		return \sfYaml::load($filename);
	}

	private function merge_translations($locale, $data, $options = array())
	{
		if (!is_array($data)) {
			return;
		}
		if (isset($this->translations[$locale])) {
			$this->translations[$locale] = array_merge_recursive($this->translations[$locale], $data);
		} else {
			$this->translations[$locale] = $data;
		}
	}
}

?>