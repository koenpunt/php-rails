<?php
	
namespace ActionView\Helpers\AssetTagHelpers;

#require 'active_support/concern'
#require 'active_support/core_ext/file'
\PHPRails::uses('action_view/helpers/asset_tag_helpers/asset_include_tag');

class JavascriptIncludeTag extends AssetIncludeTag{

	public function asset_name(){
		return 'javascript';
	}

	public function extension(){
		return 'js';
	}

	public function asset_tag($source, $options){
		return \ActionView\Helpers\TagHelper::content_tag('script', '', array_merge(array('src' => self::path_to_asset($source)), $options));
	}

	public function custom_dir(){
		return $this->config->javascripts_dir;
	}

	private function expand_sources($sources, $recursive = false){
		if(array_search('all', $sources)){
			$all_asset_files = array_push( array_diff( $this->collect_asset_files($this->custom_dir(), ($recursive ? '**' : null), "*.{$this->extension()}"), array('application') ), 'application');
			return array_unique(array_intersect($this->determine_source(\PHPRails\to_sym('defaults'), $this->expansions), $all_asset_files) + $all_asset_files);
		}else{
			$expanded_sources = array_reduce($sources, function($list, $source){
				$determined_source = $this->determine_source($source, $this->expansions);
				return $this->update_source_list($list, $determined_source);
			});
			$this->add_application_js($expanded_sources, $sources);
			return $expanded_sources;
		}
	}

	private function add_application_js($expanded_sources, $sources){
		if (array_search(\PHPRails\to_sym('defaults'), $sources) && \RFile::exist(\RFile::join($custom_dir, "application.{$extension}"))){
			\PHPRails\delete($expanded_sources, 'application');
			array_push($expanded_sources, "application");
		}
	}
}
