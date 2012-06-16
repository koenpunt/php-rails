<?php

namespace I18n\Backend;

use \I18n\I18n;
use \I18n\Helpers;

# A simple backend that reads translations from YAML files and stores them in
# an in-memory hash. Relies on the Base backend.
#
# The implementation is provided by a Implementation module allowing to easily
# extend Simple backend's behavior by including modules. E.g.:
#
# module I18n::Backend::Pluralization
#   def pluralize(*args)
#     # extended pluralization logic
#     super
#   end
# end
#
# I18n::Backend::Simple.include(I18n::Backend::Pluralization)
class Simple extends Base{
	#(class << self; self; end).class_eval { public :include }

	#module Implementation
	#  include Base
	protected $initialized = null;
	protected $translations = null;

	public function is_initialized(){
		$this->initialized = $this->initialized ?: false;
		return $this->initialized;
	}

	# Stores translations for the given locale in memory.
	# This uses a deep merge for the translations hash, so existing
	# translations will be overwritten by new ones only at the deepest
	# level of the hash.
	public function store_translations($locale, $data, $options = array()){
		$locale = Helpers\to_sym($locale);
		$this->translations[(string)$locale] = isset($this->translations[(string)$locale]) ? $this->translations[(string)$locale] : array();
		#data = data.deep_symbolize_keys
		$this->translations[(string)$locale] = Helpers\array_merge_recursive_distinct($this->translations[(string)$locale], $data);
		return $this->translations[(string)$locale];
	}

	# Get available locales from the translations hash
	public function available_locales(){
		if( !$this->is_initialized() ){
			$this->init_translations();
		}
		$locales = array();
		foreach($this->translations() as $locale => $data){
			$diff = array_diff( array_keys($data), array('i18n') );
			if( ! empty( $diff ) ){ 
				array_push($locales, $locale);
			}
		}
		return $locales;
	}

	# Clean up translations hash and set initialized to false on reload!
	public function reload(){
		$this->initialized = false;
		$this->translations = null;
		parent::reload();
	}

	protected function init_translations(){
		$this->load_translations();
		$this->initialized = true;
	}

	protected function translations(){
		$this->translations = $this->translations ?: array();
		return $this->translations;
	}

	# Looks up a translation from the translations hash. Returns nil if
	# eiher key is nil, or locale, scope or key do not exist as a key in the
	# nested translations hash. Splits keys or scopes containing dots
	# into multiple keys, i.e. <tt>currency.format</tt> is regarded the same as
	# <tt>%w(currency format)</tt>.
	protected function lookup($locale, $key, $scope = array(), $options = array()){
		if (!$this->initialized) {
			$this->init_translations();
		}
		$keys = I18n::normalize_keys($locale, $key, $scope, Helpers\get($options, 'separator'));
		return array_reduce($keys, function($result, $_key) use ($locale, $options){
			$_key = Helpers\to_sym($_key);
			if( !(Helpers\is_hash($result) && array_key_exists((string)$_key, $result) ) ){
				return null;
			} 
			$result = $result[(string)$_key];
			if ($result instanceof Symbol) {
				$result = $this->resolve($locale, $_key, $result, $options);
			}
			return $result;
		}, $this->translations);
	}
}