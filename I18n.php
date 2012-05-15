<?php

namespace I18n;

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	die('PHP I18n requires PHP 5.3 or higher');
}

define('APP', dirname(__FILE__));

require_once 'lib/option_merger.php';
require_once 'lib/exceptions.php';
require_once 'lib/backend.php';
require_once 'lib/helpers.php';
require_once 'lib/symbol.php';
require_once 'lib/utils.php';

class I18n
{
	private static $backend = null;
	private static $load_path = null;
	private static $default_locale = 'en';
	private static $default_separator = '.';
	private static $exception_handler = null;
	private static $available_locales = array();
	private static $current_locale = null;
	private static $normalized_key_cache = array();

	public static function get_backend()
	{
		if (self::$backend === null) {
			self::$backend = new Backend\Base();
		}
		return self::$backend;
	}

	public static function set_backend($backend)
	{
		self::$backend = $backend;
	}

	public static function get_default_locale()
	{
		return self::$default_locale;
	}

	public static function set_default_locale($locale)
	{
		self::$default_locale = $locale;
	}

	public static function get_locale()
	{
		if (self::$current_locale === null) {
			self::$current_locale = self::$default_locale;
		}
		return self::$current_locale;
	}

	public static function set_locale($locale)
	{
		self::$current_locale = $locale;
	}

	public static function get_available_locales()
	{
		if (empty(self::$available_locales)) {
			self::$available_locales = self::$backend->available_locales();
		}
		return self::$available_locales;
	}

	public static function set_available_locales($locale)
	{
		self::$available_locales = $locale;
	}

	public static function get_default_separator()
	{
		return self::$default_separator;
	}

	public static function set_default_separator($separator)
	{
		self::$default_separator = $separator;
	}

	public static function get_exception_handler()
	{
		return self::$exception_handler ?: function($exception, $locale, $key, $options){
			throw $exception;
		};
	}

	public static function set_exception_handler($exception_handler)
	{
		self::$exception_handler = $exception_handler;
	}

	public static function get_load_path()
	{
		if (self::$load_path === null) {
			self::$load_path = array();
		}
		return self::$load_path;
	}

	public static function set_load_path($load_path)
	{
		self::$load_path = $load_path;
	}

	public static function push_load_path($load_path)
	{
		if (count(self::$load_path) === 0 || !in_array($load_path, self::$load_path)) {
			self::$load_path[] = $load_path;
		}
	}
	# Translates, pluralizes and interpolates a given key using a given locale,
	# scope, and default, as well as interpolation values.
	#
	# *LOOKUP*
	#
	# Translation data is organized as a nested hash using the upper-level keys
	# as namespaces. <em>E.g.</em>, ActionView ships with the translation:
	# <tt>:date => {:formats => {:short => "%b %d"}}</tt>.
	#
	# Translations can be looked up at any level of this hash using the key argument
	# and the scope option. <em>E.g.</em>, in this example <tt>I18n.t :date</tt>
	# returns the whole translations hash <tt>{:formats => {:short => "%b %d"}}</tt>.
	#
	# Key can be either a single key or a dot-separated key (both Strings and Symbols
	# work). <em>E.g.</em>, the short format can be looked up using both:
	#   I18n.t 'date.formats.short'
	#   I18n.t :'date.formats.short'
	#
	# Scope can be either a single key, a dot-separated key or an array of keys
	# or dot-separated keys. Keys and scopes can be combined freely. So these
	# examples will all look up the same short date format:
	#   I18n.t 'date.formats.short'
	#   I18n.t 'formats.short', :scope => 'date'
	#   I18n.t 'short', :scope => 'date.formats'
	#   I18n.t 'short', :scope => %w(date formats)
	#
	# *INTERPOLATION*
	#
	# Translations can contain interpolation variables which will be replaced by
	# values passed to #translate as part of the options hash, with the keys matching
	# the interpolation variable names.
	#
	# <em>E.g.</em>, with a translation <tt>:foo => "foo %{bar}"</tt> the option
	# value for the key +bar+ will be interpolated into the translation:
	#   I18n.t :foo, :bar => 'baz' # => 'foo baz'
	#
	# *PLURALIZATION*
	#
	# Translation data can contain pluralized translations. Pluralized translations
	# are arrays of singluar/plural versions of translations like <tt>['Foo', 'Foos']</tt>.
	#
	# Note that <tt>I18n::Backend::Simple</tt> only supports an algorithm for English
	# pluralization rules. Other algorithms can be supported by custom backends.
	#
	# This returns the singular version of a pluralized translation:
	#   I18n.t :foo, :count => 1 # => 'Foo'
	#
	# These both return the plural version of a pluralized translation:
	#   I18n.t :foo, :count => 0 # => 'Foos'
	#   I18n.t :foo, :count => 2 # => 'Foos'
	#
	# The <tt>:count</tt> option can be used both for pluralization and interpolation.
	# <em>E.g.</em>, with the translation
	# <tt>:foo => ['%{count} foo', '%{count} foos']</tt>, count will
	# be interpolated to the pluralized translation:
	#   I18n.t :foo, :count => 1 # => '1 foo'
	#
	# *DEFAULTS*
	#
	# This returns the translation for <tt>:foo</tt> or <tt>default</tt> if no translation was found:
	#   I18n.t :foo, :default => 'default'
	#
	# This returns the translation for <tt>:foo</tt> or the translation for <tt>:bar</tt> if no
	# translation for <tt>:foo</tt> was found:
	#   I18n.t :foo, :default => :bar
	#
	# Returns the translation for <tt>:foo</tt> or the translation for <tt>:bar</tt>
	# or <tt>default</tt> if no translations for <tt>:foo</tt> and <tt>:bar</tt> were found.
	#   I18n.t :foo, :default => [:bar, 'default']
	#
	# *BULK LOOKUP*
	#
	# This returns an array with the translations for <tt>:foo</tt> and <tt>:bar</tt>.
	#   I18n.t [:foo, :bar]
	#
	# Can be used with dot-separated nested keys:
	#   I18n.t [:'baz.foo', :'baz.bar']
	#
	# Which is the same as using a scope option:
	#   I18n.t [:foo, :bar], :scope => :baz
	#
	# *LAMBDAS*
	#
	# Both translations and defaults can be given as Ruby lambdas. Lambdas will be
	# called and passed the key and options.
	#
	# E.g. assuming the key <tt>:salutation</tt> resolves to:
	#   lambda { |key, options| options[:gender] == 'm' ? "Mr. %{options[:name]}" : "Mrs. %{options[:name]}" }
	#
	# Then <tt>I18n.t(:salutation, :gender => 'w', :name => 'Smith') will result in "Mrs. Smith".
	#
	# It is recommended to use/implement lambdas in an "idempotent" way. E.g. when
	# a cache layer is put in front of I18n.translate it will generate a cache key
	# from the argument values passed to #translate. Therefor your lambdas should
	# always return the same translations/values per unique combination of argument
	# values.
	public static function translate(){
		$args     = func_get_args();
		$options  = is_hash(end($args)) ? array_pop($args) : array();
		$key      = array_shift($args);
		$backend  = self::get_backend();
		$locale   = delete($options, 'locale') ?: self::get_locale();
		$handling = delete($options, 'throw') ? 'throw' : ( delete($options, 'raise') ? 'raise' : '' ); # TODO deprecate :raise
		
		if(is_string($key) && empty($key)){
			throw new \InvalidArgumentException();
		}
		
		try {
			if(is_array($key)){
				return array_map(function($k) use ($backend, $locale, $options){ 
					return $backend->translate($locale, $k, $options);
				}, $key);
			}else{
				return $backend->translate($locale, $key, $options);
			}
		} catch (MissingTranslation $result) {
			self::handle_exception($handling, $result, $locale, $key, $options);
		}
	}
	
	public static function t(){
		return call_user_func_array(array(get_called_class(), 'translate_exception'), func_get_args());
	}

	public static function translate_exception($key, $options = array())
	{
		$options['raise'] = true;
		return self::translate($key, $options);
	}

	# Localizes certain objects, such as dates and numbers to local formatting.
	public static function localize($object, $options = array()){
		$locale = delete($options, 'locale') ?: self::get_locale();
		$format = delete($options, 'format') ?: 'default';
		return self::get_backend()->localize($locale, $object, $format, $options);
	}
	
	public static function l($object, $options = array()){
		return self::localize($object, $options);
	}
	
	public static function interpolate($string, $values){
		return Backend\InterpolationCompiler::interpolate($string, $values);
	}
	
	public static function normalize_keys($locale, $key, $scope, $separator = null)
	{
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
		#var_dump($key);
			$keys[] = explode(self::$default_separator, $key);
		}
		$keys = array_flatten($keys);
		array_map('to_sym', $keys);
		return $keys;
	}
	
	# Merges the given locale, key and scope into a single array of keys.
	# Splits keys that contain dots into multiple keys. Makes sure all
	# keys are Symbols.
	#public static function normalize_keys($locale, $key, $scope, $separator = null){
	#	$separator = $separator ?: self::$default_separator;
    #
	#	$keys = array();
	#	$keys = array_merge($keys, self::normalize_key($locale, $separator));
	#	$keys = array_merge($keys, self::normalize_key($scope, $separator));
	#	$keys = array_merge($keys, self::normalize_key($key, $separator));
	#	return $keys;
	#}


	# An elegant way to factor duplication out of options passed to a series of
	# method calls. Each method called in the block, with the block variable as
	# the receiver, will have its options merged with the default +options+ hash
	# provided. Each method called on the block variable must take an options
	# hash as its final argument.
	#
	# Without <tt>with_options></tt>, this code contains duplication:
	#
	#   class Account < ActiveRecord::Base
	#     has_many :customers, :dependent => :destroy
	#     has_many :products,  :dependent => :destroy
	#     has_many :invoices,  :dependent => :destroy
	#     has_many :expenses,  :dependent => :destroy
	#   end
	#
	# Using <tt>with_options</tt>, we can remove the duplication:
	#
	#   class Account < ActiveRecord::Base
	#     with_options :dependent => :destroy do |assoc|
	#       assoc.has_many :customers
	#       assoc.has_many :products
	#       assoc.has_many :invoices
	#       assoc.has_many :expenses
	#     end
	#   end
	#
	# It can also be used with an explicit receiver:
	#
	#   I18n.with_options :locale => user.locale, :scope => "newsletter" do |i18n|
	#     subject i18n.t :subject
	#     body    i18n.t :body, :user_name => user.name
	#   end
	#
	# <tt>with_options</tt> can also be nested since the call is forwarded to its receiver.
	# Each nesting level will merge inherited defaults in addition to their own.
	#
	public static function with_options($options, \Closure $yield){
		return $yield(new \ActiveSupport\OptionMerger(get_called_class(), $options));
	}

	
	# Any exceptions thrown in translate will be sent to the @@exception_handler
	# which can be a Symbol, a Proc or any other Object unless they're forced to
	# be raised or thrown (MissingTranslation).
	#
	# If exception_handler is a Symbol then it will simply be sent to I18n as
	# a method call. A Proc will simply be called. In any other case the
	# method #call will be called on the exception_handler object.
	#
	# Examples:
	#
	#   I18n.exception_handler = :default_exception_handler             # this is the default
	#   I18n.default_exception_handler(exception, locale, key, options) # will be called like this
	#
	#   I18n.exception_handler = lambda { |*args| ... }                 # a lambda
	#   I18n.exception_handler.call(exception, locale, key, options)    # will be called like this
	#
	#  I18n.exception_handler = I18nExceptionHandler.new                # an object
	#  I18n.exception_handler.call(exception, locale, key, options)     # will be called like this
	private static function handle_exception($handling, $exception, $locale, $key, $options){
		switch($handling){
			case 'throw':
				throw $exception;
			default:
				$handler = get($options, 'exception_handler') ?: self::get_exception_handler();
				switch(true){
					case $handler instanceof Symbol:
						#send(handler, exception, locale, key, options);
					default:
					return call_user_func($handler, $exception, $locale, $key, $options);
			}
		}
	}

	private static function normalize_key($key, $separator){
		/*
        normalized_key_cache[separator][key] ||=
          case key
          when Array
            key.map { |k| normalize_key(k, separator) }.flatten
          else
            keys = key.to_s.split(separator)
            keys.delete('')
            keys.map! { |k| k.to_sym }
            keys
          end
			
		*/
		if(self::normalized_key_cache($separator, $key) === false){
			switch(true){
				case is_array($key):
					$keys = array_flatten(array_map(function($k) use ($separator){
						return self::normalize_key($k, $separator);
					}, $key));
				default:
					$keys = explode($separator, (string)$key);
					$keys = array_filter($keys); # keys.delete('')
					$keys = array_map('to_sym', $keys);
			}
			self::normalized_key_cache($separator, $key, $keys);
		}
		return self::normalized_key_cache($separator, $key);
	}

	private static function normalized_key_cache($separator, $key, $value = null){
		if(is_null($value)){
			if(array_key_exists($separator, self::$normalized_key_cache) && array_key_exists((string)$key, self::$normalized_key_cache[$separator])){
				return self::$normalized_key_cache[$separator][(string)$key];
			}
			return false;
		}
		
		if(!array_key_exists($separator, self::$normalized_key_cache)){
			self::$normalized_key_cache[$separator] = array();
		}
		self::$normalized_key_cache[$separator][(string)$key] = $value;
	}
	
	# DEPRECATED. Use I18n.normalize_keys instead.
	private static function normalize_translation_keys($locale, $key, $scope, $separator = null){
		error_log("I18n.normalize_translation_keys is deprecated. Please use the class I18n.normalize_keys instead.");
		return self::normalize_keys($locale, $key, $scope, $separator);
	}
	
	# DEPRECATED. Please use the I18n::ExceptionHandler class instead.
	private static function default_exception_handler($exception, $locale, $key, $options){
		error_log("I18n.default_exception_handler is deprecated. Please use the class I18n::ExceptionHandler instead " .
					"(an instance of which is set to I18n.exception_handler by default).");
		if ($exception instanceof MissingTranslation) {
			return $exception->getMessage();
		}
		throw $exception;
	}

	
}