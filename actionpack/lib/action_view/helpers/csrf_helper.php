<?php

namespace ActionView\Helpers;

\PHPRails::import('ruby/RSecureRandom');

#module ActionView
  # = Action View CSRF Helper
  #module Helpers
class CsrfHelper{
	
	static $request_forgery_protection_method = 'reset_session';
	
	static $request_forgery_protection_token = 'authenticity_token';
	
	static $allow_forgery_protection = true;
	
	# Returns meta tags "csrf-param" and "csrf-token" with the name of the cross-site
	# request forgery protection parameter and token, respectively.
	#
	#   <head>
	#     <%= csrf_meta_tags %>
	#   </head>
	#
	# These are used to generate the dynamic forms that implement non-remote links with
	# <tt>:method</tt>.
	#
	# Note that regular forms generate hidden fields, and that Ajax calls are whitelisted,
	# so they do not use these tags.
	public static function csrf_meta_tags(){
		if(static::protect_against_forgery__()){
			$tags = array(
				TagHelper::tag('meta', array('name' => 'csrf-param', 'content' => static::$request_forgery_protection_token)),
				TagHelper::tag('meta', array('name' => 'csrf-token', 'content' => static::form_authenticity_token()))
			);
			
			return join($tags, "\n");
		}
	}

	# For backwards compatibility.
	public static function csrf_meta_tag(){
		return static::csrf_meta_tags();
	}
	
	# Turn on request forgery protection. Bear in mind that only non-GET, HTML/JavaScript requests are checked.
	#
	# Example:
	#
	#   class FooController < ApplicationController
	#     protect_from_forgery :except => :index
	#
	# You can disable csrf protection on controller-by-controller basis:
	#
	#   skip_before_filter :verify_authenticity_token
	#
	# It can also be disabled for specific controller actions:
	#
	#   skip_before_filter :verify_authenticity_token, :except => [:create]
	#
	# Valid Options:
	#
	# * <tt>:only/:except</tt> - Passed to the <tt>before_filter</tt> call. Set which actions are verified.
	# * <tt>:with</tt> - Set the method to handle unverified request. Valid values: <tt>:exception</tt> and <tt>:reset_session</tt> (default).
	public static function protect_from_forgery($options = array()){
		static::$request_forgery_protection_token = static::$request_forgery_protection_token ?: 'authenticity_token';
		if(array_key_exists('with', $options)){
			static::$request_forgery_protection_method = \PHPRails\delete($options, 'with');
		}
		#$this->callback->register('before_save', function(Model $model) { $model->set_timestamps(); }, array('prepend' => true));
		#prepend_before_filter :verify_authenticity_token, options
	}
	
	
	# The actual before_filter that is used. Modify this to change how you handle unverified requests.
	#protected static function
	public static function verify_authenticity_token(){
		if(!static::verified_request__()){
			error_log("WARNING: Can't verify CSRF token authenticity");
			static::handle_unverified_request();
			return false;
		}
	}

	# This is the method that defines the application behavior when a request is found to be unverified.
	# By default, \Rails uses <tt>request_forgery_protection_method</tt> when it finds an unverified request:
	#
	# * <tt>:reset_session</tt> - Resets the session.
	# * <tt>:exception</tt>: - Raises ActionController::InvalidAuthenticityToken exception.
	protected static function handle_unverified_request(){
		switch(static::$request_forgery_protection_method){
			case 'exception':
				throw new \ActionController\InvalidAuthenticityToken();
				break;
			case 'reset_session':
				@session_destroy();
				break;
			default:
				new InvalidArgumentException('Invalid request forgery protection method, use :exception or :reset_session');
				break;
		}
	}

	# Returns true or false if a request is verified. Checks:
	#
	# * is it a GET request?  Gets should be safe and idempotent
	# * Does the form_authenticity_token match the given token value from the params?
	# * Does the X-CSRF-Token header match the form_authenticity_token
	protected static function verified_request__(){
		return !static::protect_against_forgery__() || strtolower($_SERVER['REQUEST_METHOD']) == 'get' || 
			static::form_authenticity_token() == $_REQUEST[static::$request_forgery_protection_token] || 
			static::form_authenticity_token() == $_SERVER['HTTP_X_CSRF_TOKEN'];
	}

	# Sets the token value for the current session.
	/*protected*/
	public static function form_authenticity_token(){
		if(!isset($_SESSION['_csrf_token'])){
			$_SESSION['_csrf_token'] = RSecureRandom::base64(32);
		}
		return $_SESSION['_csrf_token'];
	}

	# The form's authenticity parameter. Override to provide your own.
	protected static function form_authenticity_param(){
		return $_REQUEST[static::$request_forgery_protection_token];
	}
	/*protected*/
	public static function protect_against_forgery__(){
		return static::$allow_forgery_protection;
	}

}