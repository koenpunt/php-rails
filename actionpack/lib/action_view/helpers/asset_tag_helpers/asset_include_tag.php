<?php

namespace ActionView\Helpers\AssetTagHelper;

#require 'active_support/core_ext/class/attribute'
#require 'active_support/core_ext/string/inflections'
#require 'active_support/core_ext/file'
\PHPRails::import('action_view/helpers/tag_helper');

\PHPRails::import('ruby/RFile');

class AssetIncludeTag{
	#include TagHelper

	#attr_reader :config, :asset_paths
	#class_attribute :expansions

	#public function self.inherited(base){
	#  base.expansions = { }
	#}
	protected $config;

	protected $asset_paths;

	public function __construct($config, $asset_paths){
		$this->config = $config;
		$this->asset_paths = $asset_paths;
	}

	public function asset_name(){
		throw new \PHPRails\NotImplementedError();
	}

	public function extension(){
		throw new \PHPRails\NotImplementedError();
	}

	public function custom_dir(){
		throw new \PHPRails\NotImplementedError();
	}

	public function asset_tag($source, $options){
		throw new \PHPRails\NotImplementedError();
	}

	public function include_tag(/* $sources */){
		$sources = func_get_args();
		$options = \PHPRails\extract_options($sources);
		$concat  = \PHPRails\delete($options, "concat");
		$cache   = $concat ?: \PHPRails\delete($options, "cache");
		$recursive = \PHPRails\delete($options, "recursive");

		if( $concat || ($this->config->perform_caching && $cache) ){
			$joined_name = ($cache == true ? "all" : $cache) + ".{$extension}";
			$joined_path = \RFile::join((preg_match('/^#{File::SEPARATOR}/', $joined_name) ? $this->config->assets_dir : $custom_dir), $joined_name);
			if(! ($this->config->perform_caching && \RFile::exists($joined_path)) ){
				$this->write_asset_file_contents($joined_path, $this->compute_paths($sources, $recursive));
			}
			$this->asset_tag($joined_name, $options);
		}else{
			$sources = $this->expand_sources($sources, $recursive);
			if( $cache ){
				$this->ensure_sources_($sources);
			}
			return \PHPRails\html_safe(implode("\n", array_map(function($source) use ($options){
				return $this->asset_tag($source, $options);
			}, $sources)));
		}
	}

	# PRIVATE

	protected function path_to_asset($source, $options = array()){
		return $this->asset_paths->compute_public_path($source, \ActiveSupport\Inflector::pluralize($asset_name), array_merge($options, array('ext' => $this->extension())));
	}

	private function path_to_asset_source($source){
		return $this->asset_paths->compute_source_path($source, \ActiveSupport\Inflector::pluralize($asset_name), $this->extension());
	}

	private function compute_paths(/* $args */){
		$args = func_get_args();
	#	$this->expand_sources(*args).collect { |source| path_to_asset_source(source) }
	}

	private function expand_sources($sources, $recursive){
		if( reset($sources) == 'all' ){
			return $this->collect_asset_files($this->custom_dir, ($recursive ? '**' : null), "*.{$extension}");
		}else{
			return array_reduce($sources, function($list, $source){
				$determined_source = $this->determine_source($source, $this->expansions);
				return $this->update_source_list($list, $determined_source);
			}, array());
		}
	}

	private function update_source_list(&$list, $source){
		switch(true){
			case is_string($source):
				\PHPRails\delete($list, $source);
				return array_push($list, $source);
			case is_array($source):
				$updated_sources = array_diff($source, $list);
				return array_merge($list, $updated_sources);
		}
	}

	private function ensure_sources_($sources){
		foreach($sources as $source){
			$this->asset_file_path_($this->path_to_asset_source($source));
		}
	}

	private function collect_asset_files(/* $path */){
		$path = func_get_args();
		$dir = reset($path);

		$asset_files = array_map( function($file) use ($dir){
			return preg_replace('/\.\w+$/', '', substr($file, -(strlen($file) - strlen($dir) - 1), -1));
		}, \RDir::glob(call_user_func_array(array('\RFile', 'join'), array_filter($path))));
		
		sort($asset_files);
		return $asset_files;
	}

	private function determine_source($source, $collection){
		switch(true){
			case is_a($source, '\RSymbol'):
				if($collection[$source]){
					return $collection[$source];
				}
				throw new \ArgumentError("No expansion found for #{source.inspect}");
			default:
				return $source;
		}
	}

	private function join_asset_file_contents($paths){
		return implode("\n\n", array_map(function($path){
			return \RFile::read($this->asset_file_path_($path, true)); 
		}));
	}

	private function write_asset_file_contents($joined_asset_path, $asset_paths){
		\RFileUtils::mkdir_p(\RFile::dirname($joined_asset_path));
		\RFile::atomic_write($joined_asset_path, function(&$cache){
			$cache->write($this->join_asset_file_contents($asset_paths));
		});

		# Set mtime to the latest of the combined files to allow for
		# consistent ETag without a shared filesystem.
		$mt = max(array_map(function($p){
			return \RFile::mtime($this->asset_file_path_($p));
		}, $asset_paths));
		return \RFile::utime($mt, $mt, $joined_asset_path);
	}

	/*
		TODO Implement Errno class, or replace with different exceptions
	*/
	private function asset_file_path_($absolute_path, $error_if_file_is_uri = false){
		if( $this->asset_paths->is_uri__($absolute_path) ){
			if( $error_if_file_is_uri ){
				#Errno::ENOENT
				throw new \RuntimeException( "Asset file {$path} is uri and cannot be merged into single file" );
			}
		}else{
			if( ! \RFile::exist($absolute_path) ){
				throw new \RuntimeException( "Asset file not found at '{$absolute_path}'" );
			}
			return $absolute_path;
		}
	}
}
