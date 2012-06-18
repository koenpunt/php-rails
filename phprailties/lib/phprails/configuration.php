<?php

namespace PHPRails;

#\PHPRails::import('active_support/deprecation');
#\PHPRails::import('active_support/ordered_options');
#\PHPRails::import('active_support/core_ext/object');
#\PHPRails::import('rails/paths');
#\PHPRails::import('rails/rack');

class Configuration{
	
	public $stylesheets_dir;
	
	public $javascripts_dir      = "public/javascripts";
	public $stylesheets_dir      = "public/stylesheets";
	public $page_cache_directory = "public";

    # Ensure readers methods get compiled
	#public $asset_path           ||= app.config.asset_path
	#public $asset_host           ||= app.config.asset_host
	#public $relative_url_root    ||= app.config.relative_url_root
	
	
}