<?php

namespace I18n\Backend;

use \I18n\Helpers;
use \I18n\I18n;
use \I18n\NotImplementedError;
use \I18n\InvalidLocale;
use \I18n\InvalidPluralizationData;
use \I18n\MissingTranslation;
use \I18n\MissingInterpolationArgument;
use \I18n\ReservedInterpolationKey;
use \I18n\UnknownFileType;
use \I18n\Symbol;

class Base extends Transliterator{
	# include I18n::Backend::Transliterator
	
	static $RESERVED_KEYS = array('scope', 'default', 'separator', 'resolve', 'object', 'fallback', 'format', 'cascade', 'throw', 'raise', 'rescue_format');
	#RESERVED_KEYS_PATTERN = /%\{(#{RESERVED_KEYS.join("|")})\}/
	
	protected $skip_syntax_deprecation = false;
	
	public function load_translations(/* *$filenames */){
		$filenames = func_get_args();
		if( empty($filenames) ){
			$filenames = I18n::get_load_path();
		}
		$filenames = Helpers\array_flatten($filenames);
		foreach($filenames as $filename){
			$this->load_file($filename);
		}
	}
	
	# This method receives a locale, a data hash and options for storing translations.
	# Should be implemented
	public function store_translations($locale, $data, $options = array()){
		throw new NotImplementedError();
	}
	
	public function translate($locale, $key, $options = array())
	{
		if (is_null($locale)){
			throw new InvalidLocale($locale);
		}
		$entry = $key ? $this->lookup($locale, $key, Helpers\get($options, 'scope'), $options) : null;

		if( empty($options)){
			$entry = $this->resolve($locale, $key, $entry, $options);
		}else{
			list($count, $default) = array(Helpers\get($options, 'count'), Helpers\get($options, 'default'));
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
			$entry = $this->pluralize($locale, $entry, $count);
		}
		if (isset($values)){
			$entry = $this->interpolate($locale, $entry, $values);
		}
		return $entry;
	}

	# Acts the same as +strftime+, but uses a localized version of the
	# format string. Takes a key from the date/time formats translations as
	# a format argument (<em>e.g.</em>, <tt>:short</tt> in <tt>:'date.formats'</tt>).
	public function localize($locale, $object, $format = null, $options = array()){
		if( !method_exists($object, 'strftime') ){
			throw new InvalidArgumentError("Object must be a Date, DateTime or Time object. {get_class($object)} given.");
		}
		if(is_null($format)){
			$format = Helpers\to_sym('default');
		}
		
		if($format instanceof Symbol){
			$key  = $format;
			$type = method_exists($object, 'sec') ? 'time' : 'date';
			$options = array_merge( $options, array('raise' => true, 'object' => $object, 'locale' => $locale));
			$format  = I18n::t("{$type}.formats.{$key}", $options);
		}

		# format = resolve(locale, object, format, options)
		$format = preg_replace_callback('/%[aAbBp]/', function($match) use ($object, $locale, $format){
			switch($match[0]){
				case '%a':
					$f = I18n::t("date.abbr_day_names",                  array('locale' => $locale, 'format' => $format));
					return $f[$object->wday()];
				case '%A':
					$f = I18n::t("date.day_names",                       array('locale' => $locale, 'format' => $format));
					return $f[$object->wday()];
				case '%b':
					$f = I18n::t("date.abbr_month_names",                array('locale' => $locale, 'format' => $format));
					return $f[$object->mon()];
				case '%B':
					$f = I18n::t("date.month_names",                     array('locale' => $locale, 'format' => $format));
					return $f[$object->mon()];
				case '%p':
					if(method_exists('hour', $object)){
						$meridian = $object->hour() < 12 ? 'am' : 'pm';
						return I18n::t("time.{$meridian}", array('locale' => $locale, 'format' => $format));
					}
					break;
			}
		}, $format);
		return $object->strftime($format);
	}

	# Returns an array of locales for which translations are available
	# ignoring the reserved translation meta data key :i18n.
	public function available_locales(){
		throw new NotImplementedError();
	}


	public function reload(){
		$this->skip_syntax_deprecation = false;
	}

	# The method which actually looks up for the translation in the store.
	protected function lookup($locale, $key, $scope = array(), $options = array()){
		throw new NotImplementedError();
	}


	# Evaluates defaults.
	# If given subject is an Array, it walks the array and returns the
	# first translation that can be resolved. Otherwise it tries to resolve
	# the translation directly.
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
		if( !(Helpers\is_hash($entry) && $count)){
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
	
	# Loads a single translations file by delegating to #load_php or
	# #load_yml depending on the file extension and directly merges the
	# data to the existing translations. Raises I18n::UnknownFileType
	# for all other file extensions.
	protected function load_file($filename){
		$filename_splitted = explode('.', $filename);
		$type = strtolower(end($filename_splitted));
		$method_name = "load_{$type}";
		if (!method_exists($this, $method_name)) {
			throw new UnknownFileType($type, $filename);
		}
		$data = $this->$method_name($filename);
		if(!Helpers\is_hash($data)){
			throw new InvalidLocaleData($filename);
		}
		foreach ($data as $locale => $d) {
			$this->store_translations($locale, $d ?: array() );
		}
	}

	# Loads a plain PHP translations file. eval'ing the file must yield
	# a Hash containing translation data with locales as toplevel keys.
	protected function load_php($filename){
		ob_start();
		include $filename;
		$out = ob_get_clean();
		return eval('return ' . $out);
	}
	
	# Loads a YAML translations file. The data must have locales as
	# toplevel keys.
	protected function load_yml($filename){
		if(!function_exists('yaml_parse_file')){
			require_once('SymfonyComponents/YAML/sfYaml.php');
			return \sfYaml::load($filename);
		}
		return yaml_parse_file($filename);
	}
}

?>
