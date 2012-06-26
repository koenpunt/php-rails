<?php

#require 'thread'
#require 'active_support/core_ext/file'
#require 'active_support/core_ext/module/attribute_accessors'

namespace ActionView\Helpers\AssetTagHelper;

class AssetPaths extends \ActionView\AssetPaths{ #:nodoc:
	# You can enable or disable the asset tag ids cache.
	# With the cache enabled, the asset tag helper methods will make fewer
	# expensive file system calls (the default implementation checks the file
	# system timestamp). However this prevents you from modifying any asset
	# files while the server is running.
	#
	#   ActionView::Helpers::AssetTagHelper::AssetPaths.cache_asset_ids = false
	#mattr_accessor :cache_asset_ids
	static $cache_asset_ids = null;

	# Add or change an asset id in the asset id cache. This can be used
	# for SASS on Heroku.
	# :api: public
	public function add_to_asset_ids_cache($source, $asset_id){
		self::$asset_ids_cache_guard[$source] = $asset_id;
		#self.asset_ids_cache_guard.synchronize do
		#  self.asset_ids_cache[source] = asset_id
		#end
	}

	protected function rewrite_extension($source, $dir, $ext){
		$source_ext = \RFile::extname($source);
		
		if( empty($source_ext) ){
			$source_with_ext = "{$source}.{$ext}";
		}elseif( $ext != substr($source_ext, 1, -1) ){
			$with_ext = "{$source}.{$ext}";
			if( \RFile::exist(\RFile::join($config->assets_dir, $dir, $with_ext))) {
				return $with_ext;
			}
		}
		
		return $source_with_ext ?: $source;
	}

	# Break out the asset path rewrite in case plugins wish to put the asset id
	# someplace other than the query string.
	protected function rewrite_asset_path($source, $dir = null){
		if( !($source[0] == '?/') ){ //== ?/) ){
			$source = "/{$dir}/{$source}";
		}
		$path = $config->asset_path;

		if( $path && method_exists($path, 'call') ){
			return $path->call($source);
		}elseif( $path && is_string($path) ){
			return $path % array($source);
		}

		$asset_id = $this->rails_asset_id($source);
		if( empty($asset_id) ){
			return $source;
		}else{
			return "{$source}?{$asset_id}";
		}
	}

	//mattr_accessor :asset_ids_cache
	//self.asset_ids_cache = {}
	static $asset_ids_cache = array();
	//
	//mattr_accessor :asset_ids_cache_guard
	//self.asset_ids_cache_guard = Mutex.new
	static $asset_ids_cache_guard = array();

	# Use the RAILS_ASSET_ID environment variable or the source's
	# modification time as its cache-busting asset id.
	private function rails_asset_id($source){
		if( $asset_id = \PHPRails\get($_SERVER, "RAILS_ASSET_ID") ){
			return $asset_id;
		}else{
			if( self::$cache_asset_ids && ($asset_id = self::$asset_ids_cache[$source]) ){
				return $asset_id;
			}else{
				$path = \RFile::join($config->assets_dir, $source);
				$asset_id = \RFile::exist($path) ? (string)(int)\RFile::mtime($path) : '';

				if( self::$cache_asset_ids ){
					$this->add_to_asset_ids_cache($source, $asset_id);
				}

				return $asset_id;
			}
		}
	}
}
