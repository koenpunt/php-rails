<?php

namespace ActionView\Helpers\AssetTagHelper;

#\PHPRails::import('active_support/concern');
#\PHPRails::import('active_support/core_ext/file');
\PHPRails::import('action_view/helpers/asset_tag_helpers/asset_include_tag');

class StylesheetIncludeTag extends AssetIncludeTag{
	
	public function asset_name(){
		return 'stylesheet';
	}

	public function extension(){
		return 'css';
	}

	public function asset_tag($source, $options){
		# We force the :request protocol here to avoid a double-download bug in IE7 and IE8
		\ActionView\Helpers\TagHelper::tag("link", array_merge( array( "rel" => "stylesheet", "type" => \RMime::CSS, "media" => "screen", "href" => $this->path_to_asset($source, array('protocol' => 'request')) ), $options));
	}

	public function custom_dir(){
		return $this->config->stylesheets_dir;
	}
}
