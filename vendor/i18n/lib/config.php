<?php 

namespace I18n;
class Config{
	# The only configuration value that is not global and scoped to thread is :locale.
	# It defaults to the default_locale.
	protected 
		$_locale = null,
		$_backend = null,
		$_default_locale = null,
		$_available_locales = null,
		$_default_separator = null,
		$_exception_handler = null,
		$_load_path = null;

	public function __get($var){
		switch($var){
			case 'locale':
				$this->_locale = $this->_locale ?: $this->default_locale;
				return $this->_locale;
			case 'backend':
				# Returns the current backend. Defaults to +Backend::Simple+.
				$this->_backend = $this->_backend ?: new Backend\Simple();
				return $this->_backend;
			case 'default_locale':
				# Returns the current default locale. Defaults to 'en'
				$this->_default_locale = $this->_default_locale ?: 'en';
				return $this->_default_locale;
			case 'available_locales':
				# Returns an array of locales for which translations are available.
				# Unless you explicitely set these through I18n.available_locales=
				# the call will be delegated to the backend.
				$this->_available_locales = $this->_available_locales ?: null;
				return $this->_available_locales ?: $this->backend->available_locales(); 
			case 'default_separator':
				# Returns the current default scope separator. Defaults to '.'
				$this->_default_separator = $this->_default_separator ?: '.';
				return $this->_default_separator;
			case 'exception_handler':
				# Return the current exception handler. Defaults to :default_exception_handler.
				$this->_exception_handler = $this->_exception_handler ?: new ExceptionHandler();
				return $this->_exception_handler;
			case 'load_path':
				# Allow clients to register paths providing translation data sources. The
				# backend defines acceptable sources.
				#
				# E.g. the provided SimpleBackend accepts a list of paths to translation
				# files which are either named *.rb and contain plain Ruby Hashes or are
				# named *.yml and contain YAML data. So for the SimpleBackend clients may
				# register translation files like this:
				#   I18n.load_path << 'path/to/locale/en.yml'
				$this->_load_path  = $this->_load_path ?: array();
				return $this->_load_path;
		}
	}
	
	public function __set($var, $value){
		switch($var){
			case 'locale':
				# Sets the current default locale. Used to set a custom default locale.
				$this->_locale = $value;
				break;
			case 'default_locale':
				# Sets the current default locale. Used to set a custom default locale.
				$this->_default_locale = $value;
				break;
			case 'available_locales':
				$this->_available_locales = array_map(function($locale){ return Helpers\to_sym($locale); }, $value);
				if(empty($this->_available_locales)){
					$this->_available_locales = null;
				}
				break;
			default:
				$this->{"_$var"} = $value;
		}
	}
	/*
	# Sets the current locale pseudo-globally, i.e. in the Thread.current hash.
	def locale=(locale)
	  @locale = locale.to_sym rescue nil
	end

	# Sets the current backend. Used to set a custom backend.
	def backend=(backend)
	  @@backend = backend
	end
	# Sets the current default scope separator.
	def default_separator=(separator)
	  @@default_separator = separator
	end
	# Sets the exception handler.
	def exception_handler=(exception_handler)
	  @@exception_handler = exception_handler
	end

	# Sets the load path instance. Custom implementations are expected to
	# behave like a Ruby Array.
	def load_path=(load_path)
	  @@load_path = load_path
	end
	
	*/
}