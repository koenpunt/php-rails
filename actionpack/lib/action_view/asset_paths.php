<?php

#require 'zlib'
#require 'active_support/core_ext/file'
#require 'action_controller/metal/exceptions'

namespace ActionView;
class AssetPaths{ #:nodoc:

	const URI_REGEXP = '#^[-a-z]+://|^cid:|^//#';

	#attr_reader :config, :controller

	public function __construct($config, $controller = null){
		$this->config = $config;
		$this->controller = $controller;
	}

	# Add the extension +ext+ if not present. Return full or scheme-relative URLs otherwise untouched.
	# Prefix with <tt>/dir/</tt> if lacking a leading +/+. Account for relative URL
	# roots. Rewrite the asset path for cache-busting asset ids. Include
	# asset host, if configured, with the correct request protocol.
	#
	# When :relative (default), the protocol will be determined by the client using current protocol
	# When :request, the protocol will be the request protocol
	# Otherwise, the protocol is used (E.g. :http, :https, etc)
	public function compute_public_path($source, $dir, $options = array()){
		$source = (string)$source;
		if( $this->is_uri__($source) ){
			return $source;
		}

		if( \PHPRails\get($options, 'ext') ){
			$source = $this->rewrite_extension($source, $dir, $options['ext']);
		}
		$source = $this->rewrite_asset_path($source, $dir, $options);
		$source = $this->rewrite_relative_url_root($source, $relative_url_root);
		$source = $this->rewrite_host_and_protocol($source, $options['protocol']);
		return $source;
	}

	# Return the filesystem path for the source
	public function compute_source_path($source, $dir, $ext){
		if( $ext ){
			$source = $this->rewrite_extension($source, $dir, $ext);
		}
		return \RFile::join($config->assets_dir, $dir, $source);
	}

	public function is_uri__($path){
		return preg_match(self::URI_REGEXP, $path) > 0;
	}

	private function rewrite_extension($source, $dir, $ext){
		throw new \NotImplementedError();
	}

	private function rewrite_asset_path($source, $path = null){
		throw new \NotImplementedError();
	}

	private function rewrite_relative_url_root($source, $relative_url_root){
		return $relative_url_root && strpos($source, "{$relative_url_root}/") > 0 ? "{$relative_url_root}#{$source}" : $source;
	}

	private function has_request__(){
		#controller.respond_to?(:request)
		return $controller->request;
	}

	private function rewrite_host_and_protocol($source, $protocol = null){
		$host = $this->compute_asset_host($source);
		if( $host && !$this->is_uri__($host)){
			if( ($protocol == 'request' || $default_protocol == 'request') && $this->has_request__() ){
				$host = null;
			}else{
				$host = sprintf("%s{$host}", $this->compute_protocol($protocol));
			}
		}
		return $host ? "{$host}#{$source}" : $source;
	}

	private function compute_protocol($protocol){
		$protocol = $protocol ?: self::default_protocol();
		switch($protocol){
			case 'relative';
				return "//";
			case 'request':
				if(!$this->controller){
					$this->invalid_asset_host_("The protocol requested was :request. Consider using :relative instead.");
				}
				return $this->controller->request->protocol();
			default:
				return "{$protocol}://";
		}
	}

	private function default_protocol(){
		return $this->config->default_asset_host_protocol ?: ($this->has_request__() ? 'request' : 'relative');
	}
	
	private function invalid_asset_host_($help_message){
		throw new \ActionController\RoutingError("This asset host cannot be computed without a request in scope. {$help_message}");
	}

	# Pick an asset host for this source. Returns +nil+ if no host is set,
	# the host if no wildcard is set, the host interpolated with the
	# numbers 0-3 if it contains <tt>%d</tt> (the number is the source hash mod 4),
	# or the value returned from invoking call on an object responding to call
	# (proc or otherwise).
	private function compute_asset_host($source){
		if( $host = $this->asset_host_config()){
			if( method_exists($host, 'call') ){
				$args = array($source);
				$arity = $this->arity_of($host);
				if( ($arity > 1 || $arity < -2) && $this->has_request__() ){
					$this->invalid_asset_host_("Remove the second argument to your asset_host Proc if you do not need the request, or make it optional.");
				}
				if( ($arity > 1 || $arity < 0) && $this->has_request__() ){
					array_push($args, $this->current_request());
				}
				$host->call($args);
			}else{
				return (preg_match('/%d/', $host) > 0 ? $host % (crc32($source) % 4) : $host);
			}
		}
	}

	private function relative_url_root(){
		return $config->relative_url_root ?: $this->current_request->script_name();
	}

	private function asset_host_config(){
		return $this->config->asset_host;
	}

	# Returns the current request if one exists.
	private function current_request(){
		if($this->has_request__()){
			return $this->controller->request;
		}
	}

	# Returns the arity of a callable
	private function arity_of($callable){
		$method = new \RMethod($callable, 'call');
		return is_object($callable) && method_exists($callable, 'arity') ? $callable->arity() : $method->arity();
	}
}